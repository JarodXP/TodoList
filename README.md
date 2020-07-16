# Welcome on ToDoList  

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
