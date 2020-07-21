# Welcome on ToDoList  

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=JarodXP_TodoList&metric=alert_status)](https://sonarcloud.io/dashboard?id=JarodXP_TodoList)  [![Coverage](https://sonarcloud.io/api/project_badges/measure?project=JarodXP_TodoList&metric=coverage)](https://sonarcloud.io/dashboard?id=JarodXP_TodoList)  

This project is issued from the Openclassrooms Symfony course.  
This is the context of the project:  
As a developer, you arrive in a startup where a minimum viable product has been built.  
The goal is to make a quality and performance audit on what has been developped and bring some new features.

## Requirements  

- PHP 7.4 or higher
- [optional] php extension pdo_sqlite (in case of using SQLite as database)
- Composer

## Installation

1. Clone the Github repository: https://github.com/JarodXP/TodoList  
2. Create a .env.local file including the following variables:
APP_ENV=dev  
APP_SECRET='To be defined'  
DATABASE_URL='To be defined' (recommended: use SQLite: DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db)  
3. Run a composer install  

## Setup  

Run the following commands:  

1. `php bin\console doctrine:database:create`
2. `php bin\console doctrine:schema:create`
3. `php bin\console hautelook:fixtures:load`  
