# Salary Payment [PHP Test]

## Install instructions

- cd backend
- composer install

## How to use

- php artisan salary:create_sheet (creates a full CSV for the current year)
- php artisan salary:create_sheet --month={1-12} (you can select the month)
- php artisan salary:create_sheet --year={1970-20xx} (you can select the year)