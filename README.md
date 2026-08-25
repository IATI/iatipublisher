<h1> IATI Publisher</h1> <a href="https://www.codefactor.io/repository/github/iati/iatipublisher"><img src="https://www.codefactor.io/repository/github/iati/iatipublisher/badge" alt="CodeFactor" /></a> <br/><br/>


IATI Publisher is a free-to-use publishing tool built using Laravel framework which will provide a simplified, intuitive, robust and more efficient process for publishers to add, modify and update their IATI data.

IATI – the International Aid Transparency Initiative – brings together governments, multilateral institutions, private sector and civil society organizations and others to increase the transparency and openness of resources flowing into developing countries. To learn more about IATI, please visit the [iatistandard.org](https://iatistandard.org/).


## Prerequisite
The prerequisite for this project are:
- php>=8.2
- node: 16
- postgres: 14
- composer: 2
- redis: 7
- nginx server

## Install
IATI Publisher can be cloned from GitHub repository and installed as follows:
- git clone https://github.com/iati/iatipublisher.git<br />
  OR<br />
  git clone git@github.com:iati/iatipublisher.git
- cd iatipublisher

## Run
In order to run this app follow the commands listed below:
- Install the application dependencies with commands: `composer install` and `npm install`
- Copy .env.example file to .env file and update your application configuration related to app_name, app_debug, logs, database, filesystem, queue driver, redis, mail, IATI endpoints, encryption key, storage path.
- Generate APP_KEY with command : `php artisan key:generate`
- Run migration and seed the database with command : `php artisan migrate:fresh --seed`
- Serve the application using `php artisan serve` (append --port PORT_NUMBER to run in a port other than 8000)
- Enable horizon with command `php artisan horizon`
- Access `localhost:8000` to run application in browser(`localhost:PORT_NUMBER` if different port number is specified)

Note: Make sure postgresql and redis services are up and running



## Framework
This application is coded in PHP using [Laravel](https://laravel.com/docs/12.x) framework. The version of Laravel used for this project is [12.0](https://laravel.com/docs/12.x) . The application also uses [Vue 3](https://vuejs.org) (Composition API), [Typescript](https://www.typescriptlang.org) and [Tailwind](https://tailwindcss.com/).



## Tools and packages

The packages used in this application can be seen in [composer.json](https://github.com/iati/iatipublisher/blob/main/composer.json) and [package.json](https://github.com/iati/iatipublisher/blob/main/package.json) file. Some of the important packages and dependencies used are:
- Sass: ^1.32.11
- Vue: ^3.0.23
- Typescript: ^4.6.3
- Tailwindcss: ^3.0.23
- Axios: ^0.25
- Husky: ^7.0.4
- Prettier: ^2.5.1
- Postcss: ^8.4.6
- Stylelint: ^14.5.1
- Autoprefixer: ^10.4.2
- Friendsofphp/php-cs-fixer: ^3.6
- Laravel/ui: ^3.4
- Predis/predis: ^2.0
- Sabre/xml: ^4.0


## Structure
This application is structured in a simple way within `app\IATI` folder. The various folders and their corresponding contents are:
- Models : Contains all the eloquent model classes.
- Repositories : Contains all the classes for storage and retrieval from database.
- Services : Contains the classes which serves as a bridge between Controller and Repositories. All the application logic are handled here.

The Typescript and Vue related files are placed within `resources\assets\js` folder. The various folders and their corresponding contents are:
- components : Contains all the reusable Vue components.
- views : Contains the Vue pages and their partial components.
- scripts : Contains Typescript to manipulate DOM.
The Laravel blade file are placed within the folder present in `resources\views`. The Vue components are loaded into these blade files.

The sass files are present within `resource\assets\sass` folder. The scss are places using 7-1 folder structure. The component specific styling are present within the script tag of the corresponding component.

These Sass, Typescript and Vue files are bundled with the help of webpack into public folder. To see the changes made into these files use the command `npm run watch`.

## Queue

IATI Publisher uses Laravel Queue for specific purposes like importing activities and bulk publishing activities. The queue uses redis as queue driver. The configuration for redis and queue can be setup locally in three ways:
- Manually running artisan command `php artisan horizon`. [Horizon](https://laravel.com/docs/9.x/horizon#configuration) can be paused with `php artisan horizon:pause` command and terminated with `php artisan horizon:terminate` command. To check the status of horizon, use command `php artisan horizon:status`.

- Using [supervisorctl](https://laravel.com/docs/9.x/queues#supervisor-configuration) to run queue

- Manually running artisan command `php artisan queue:work` or `php artisan queue:listen` to start queue worker. The command `php artisan queue:work` needs to be restarted every time the changes are made in the code. To run the queue continuously, without having to restart on changes, use command `php artisan queue:listen`.

## Test

The application uses PHPUnit, the built-in testing support of Laravel for unit and feature tests. The test files are included within `tests` folder. Following commands can be used to run test cases:

- Run all tests: `php artisan test`
- Run specific test class: `php artisan test --filter [Test Class]`
- Run specific test method from a class : `php artisan test --filter [Test Class]::[Method Name]`

## Generate a Sealed-Secret using kubeseal

### Prerequisites
Before you begin, you should have the following:

- `kubectl` installed on your local machine
- `kubeseal` installed on your local machine
- `secret.yaml` file on your local machine and private.
- `seal-secret.sh` script that will seal `secret.yaml` file to `sealed-secret.yaml` file using `kubeseal`
- `seal-secret.sh` and `secret.yaml` must be within a same directory.

Run the below command to generate sealed secret from `secret.yaml` file:

```
./seal-secret.sh
```

Note: Make sure to keep the secret.yaml file private, as they contain sensitive information.
