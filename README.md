# YoPrint - CSV Upload & Processing System

A Laravel-based application for uploading and processing CSV files with background job processing, real-time status updates, and idempotent data management.

## Features

- **Drag & Drop File Upload** - User-friendly CSV file upload interface
- **Background Processing** - Asynchronous CSV processing using Laravel Horizon
- **Real-time Status Updates** - Live polling of file processing status (pending → processing → completed/failed)
- **Idempotent Operations** - Upload the same file multiple times; existing products are updated by `unique_key`
- **UTF-8 Character Cleaning** - Automatic encoding conversion and invalid character removal
- **API Transformers** - Consistent data transformation across API and views
- **Queue Monitoring** - Laravel Horizon dashboard for job monitoring

## Tech Stack

- **Framework:** Laravel 12.37.0
- **PHP:** 8.3.27
- **Database:** SQLite
- **Queue:** Redis with Laravel Horizon
- **Frontend:** Tailwind CSS (via CDN)
- **Timezone:** Asia/Kuala_Lumpur

## Installation

### Prerequisites

- PHP 8.3+
- Composer
- Redis server
- Laravel Herd (or similar local development environment)

### Setup

1. **Clone the repository**
   ```bash
   cd /path/to/project
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure environment**
   The `.env` file should have:
   ```env
   DB_CONNECTION=sqlite
   QUEUE_CONNECTION=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start Laravel Horizon**
   ```bash
   php artisan horizon
   ```

7. **Access the application**
   - Main app: `http://localhost` (or your Herd URL)
   - Horizon dashboard: `http://localhost/horizon`

## Database Schema

### `file_uploads` Table
| Column | Type | Description |
|--------|------|-------------|
| id | integer | Primary key |
| file_name | string | Stored filename (timestamped) |
| original_name | string | Original uploaded filename |
| status | enum | pending/processing/failed/completed |
| created_at | timestamp | Upload time |
| updated_at | timestamp | Last update time |

### `products` Table
| Column | Type | Description |
|--------|------|-------------|
| unique_key | string | Primary key - unique product identifier |
| product_title | string | Product title |
| product_description | text | Product description (nullable) |
| style# | string | Style number (nullable) |
| sanmar_mainframe_color | string | Color from mainframe (nullable) |
| size | string | Product size (nullable) |
| color_name | string | Color name (nullable) |
| piece_price | decimal(10,2) | Price per piece (nullable) |
| created_at | timestamp | First created |
| updated_at | timestamp | Last updated |

## CSV Format

Your CSV file should contain the following columns (in order):

| Index | Column Name | Description |
|-------|-------------|-------------|
| 0 | UNIQUE_KEY | Unique product identifier |
| 1 | PRODUCT_TITLE | Product title |
| 2 | PRODUCT_DESCRIPTION | Product description |
| 3 | STYLE# | Style number |
| 14 | COLOR_NAME | Color name |
| 18 | SIZE | Product size |
| 21 | PIECE_PRICE | Price per piece |
| 28 | SANMAR_MAINFRAME_COLOR | Mainframe color |

**Note:** The CSV mapping uses specific column indices. Ensure your CSV structure matches these requirements.

## API Endpoints

### Web Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Main page - displays uploaded files |
| POST | `/upload` | Upload CSV file |
| GET | `/upload/status/{id}` | Get file processing status (JSON) |
| GET | `/horizon` | Horizon dashboard (local env only) |

### Upload Response
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "file": {
    "id": 1,
    "file_name": "1762404055_products.csv",
    "original_name": "products.csv",
    "status": "pending",
    "status_color": "bg-gray-100 text-gray-800",
    "created_at": "2025-11-06 12:31pm",
    "created_at_timestamp": 1730898703,
    "relative_time": "5 seconds ago",
    "updated_at": "2025-11-06 12:31pm"
  }
}
```

## Usage

### Uploading a CSV File

1. Navigate to the main page
2. Drag and drop a CSV file onto the upload box, or click to select a file
3. Click the "Upload File" button
4. The file status will automatically update as it's processed

### Re-uploading for Updates

You can upload the same file multiple times. The system will:
- Create a new upload record
- Process all rows in the CSV
- **Update** existing products (matched by `unique_key`)
- **Insert** new products (if `unique_key` doesn't exist)

This makes it easy to edit product data in your CSV and re-upload to update the database.

### Monitoring with Horizon

1. Navigate to `/horizon`
2. View real-time job processing
3. Monitor failed jobs
4. Check job metrics and throughput

## Architecture

### File Processing Flow

```
1. User uploads CSV file
   ↓
2. File stored in storage/app/private/uploads/
   ↓
3. FileUpload record created with status='pending'
   ↓
4. ProcessCsvFile job dispatched to queue
   ↓
5. Horizon worker picks up job
   ↓
6. Status updated to 'processing'
   ↓
7. CSV parsed with UTF-8 cleaning
   ↓
8. Products upserted in chunks of 100
   ↓
9. Status updated to 'completed' or 'failed'
```

### Key Components

**Controllers:**
- `FileUploadController` - Handles upload, index, and status endpoints

**Models:**
- `FileUpload` - File upload records
- `Product` - Product data with custom primary key

**Jobs:**
- `ProcessCsvFile` - Background CSV processing with chunking

**Resources:**
- `FileUploadResource` - API transformer for consistent responses

**Views:**
- `welcome.blade.php` - Main upload interface with real-time updates

## Features in Detail

### UTF-8 Character Cleaning

All CSV fields are automatically:
- Converted to UTF-8 encoding
- Stripped of invalid UTF-8 characters
- Trimmed of whitespace
- Handles Byte Order Mark (BOM)

### Upsert Logic

Products are upserted using `unique_key`:
```php
Product::upsert(
    $products,
    ['unique_key'], // Match on this column
    ['product_title', 'product_description', ...] // Update these columns
);
```

**Result:**
- Existing products → Updated
- New products → Inserted
- No duplicates created

### Real-time Status Polling

JavaScript polls `/upload/status/{id}` every 3 seconds for files with status `pending` or `processing`. Polling stops automatically when status changes to `completed` or `failed`.

## Testing

Run the test suite:
```bash
php artisan test
```

Current test coverage:
- File upload validation
- CSV processing
- File listing and display
- Model behavior
- Status updates

## Troubleshooting

### Queue not processing
- Ensure Redis is running
- Start Horizon: `php artisan horizon`
- Check Horizon dashboard for failed jobs

### File not found error
- Verify file storage path is correct
- Check `storage/app/private/uploads/` directory exists
- Ensure proper file permissions

### Encoding issues
- The system automatically handles UTF-8 conversion
- Ensure your CSV is saved with UTF-8 encoding

## License

This project is built on Laravel, which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
