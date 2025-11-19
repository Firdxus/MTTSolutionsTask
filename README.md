# Product Order Summary Report - Laravel Mini Project

This is a mini Laravel application that generates and exports a sales performance report from a simulated database. The report includes summary cards, detailed tables, and an Excel export feature.

## Features
- Display total orders, total revenue, average order value, and top 3 products.
- Show detailed order items in a table.
- Export the report to Excel with styled headers and merged cells.

## Database Seeding

The project includes factories and seeders for dummy data:

CustomerFactory
CategoryFactory
ProductFactory
OrderFactory
OrderItemFactory

Running "php artisan migrate --seed" will populate the database with sample data for testing.

## Exporting Reports

Use the "Download Excel" button on the report page to export the report to "filename.xlsx".
Excel includes:
-Summary at the top left (merged and bolded cells)
-Detailed order table
-Subtotals for each order

## Installation Steps

1. Clone the repository:
git clone https://github.com/Firdxus/MTTSolutionsTask.git

2. Go into the project directory:
cd MTTSolutionsTask

3. Install dependencies:
-composer install
-npm install
-npm run dev

4. Set up environment file and make sure it has correct database credentials and APP_URL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salesperformancereport
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost

5. Generate application key:
php artisan key:generate

6. Run migrations and seed the database:
php artisan migrate --seed

7. Run the application:
php artisan serve

8. Open in your browser (or you will get the link after you run "php artisan serve"): 
http://127.0.0.1:8000/report

## Packages Used
Maatwebsite Excel – for Excel export.

## Usage
Visit /report in your browser.
Select a start date and end date for the report.
Click Filter to view the summary and detailed orders.
Click Download Excel to export the report to xlsx file.
