# News Hive

**News Hive** is a Laravel news aggregator backend developed as a take-home assignment for a Backend Web Developer position at [Innoscripta](https://www.innoscripta.com/). It fetches articles from various sources and provides a powerful API for frontend applications.

## Note For Reviewer

Dear Reviewer,

The main challenge I faced during this challenge was finding suitable news sources.  
Of the 7 sources suggested, some were disabled, and others were not well-suited for this specific project (please see the [Sources selection](#sources)).

Despite this challenge, I have completed the task to the best of my engineering abilities and believe the implemented solution effectively addresses the requirements.

## Features

- Aggregates news articles from multiple sources (currently: Guardian, NYTimes, ESPN)
- Offers rich filtering options by date, category, source, etc.
- Optimized for performance with Laravel Octane & FrankenPHP
- Includes comprehensive API documentation in JSON OpenAPI 3.1.0 format
- Dockerized for easy deployment and scaling
- Feature testing with phpunit and code coverage > 80%
- RESTful API for accessing news data
- User-friendly command-line interface for managing the application


## Live Demo

A live demo application is running at: [https://news-hive.yeganemehr.net](https://news-hive.yeganemehr.net)

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL or any other supported database
- Docker (optional, for containerized development)


## Installation

1. Clone the repository:

    ```sh
    git clone https://github.com/yeganemehr/news-hive.git
    cd news-hive
    ```

2. Install dependencies:

    ```sh
    composer install
    ```

3. Copy the example environment file and configure the environment `GARDIAN_API_KEY` & `NYTIMES_API_KEY` variables:

    ```sh
    cp .env.example .env
    ```

4. Generate the application key:

    ```sh
    php artisan key:generate
    ```

5. Run the database migrations:

    ```sh
    php artisan migrate
    ```

6. Seed the database with initial data:

    ```sh
    php artisan db:seed
    ```

7. Start the development server:

    ```sh
    php artisan serve
    ```

## Usage

### Aggregating News Articles

To fetch and save news articles from the configured sources, run the following command:

```sh
php artisan fetch
```


## API Documentation

* JSON OpenAPI 3.1.0 specification: http://YOUR-HOSTNAME.local/docs/v1.json
* Interactive API documentation UI: http://YOUR-HOSTNAME.local/docs/v1


## Testing

Feature tests are located in the `tests` directory.   
Run tests with:

```bash
php artisan test --coverage
```

I maintain code coverage of **over 80%**.


## Optimization

This project is built with performance in mind and leverages the following technologies:

1. [Laravel Octane](https://laravel.com/docs/11.x/octane)
2. [FrankenPHP](https://frankenphp.dev/): is a extremely fast application server for PHP built on top of Caddy.

## Docker:

This project is fully containerized using Docker for easy deployment and scalability.  

Key features:

1. FrankenPHP **Alpine Base Image**: The Docker image is based on the lightweight Alpine Linux distribution and FrankenPHP.

2. **Supervisor** is used to manage the processes within the container, ensuring that both Laravel Octane and the scheduler (for fetching new articles) are running reliably.

3. **docker-compose.yaml** is provided for easy orchestration of the development environment, including database setup.

4. **Dockerfile** provides a clear and reproducible build process for the Docker image.


## CI/CD

This project utilizes GitHub Actions for continuous integration and delivery (CI/CD). Two workflows are configured to automate tasks upon code changes:

**[ci.yaml](.github/workflows/ci.yaml):**

This workflow focuses on code quality and testing:

* **Lint:** Runs `laravel/pint` to check code style adherence to Laravel coding standards.
* **Tests:** Executes the project's test suite using `php artisan test` to ensure functionality.
* **OpenAPI Validation:** Analyzes the OpenAPI documentation for any errors or inconsistencies.

**[dockerize.yaml](.github/workflows/dockerize.yaml):**

This workflow automates Docker image management:

Build the Docker image upon every push to the repository and pushes the built Docker image to the project's container registry on GitHub Packages: [news-hive](https://github.com/yeganemehr/news-hive/pkgs/container/news-hive)


## Sources
Here's an overview of the evaluated news sources and their suitability for this project:

1. **NewsAPI**: As NewsAPI is also a news aggregator, integrating it felt redundant for this project, where building our own aggregation was the goal.

2. **OpenNews**: Unfortunately, I couldn't locate API for OpenNews.

3. **NewsCred**: My research indicated that NewsCred no longer operates.

4. **The Guardian**: The Guardian API was successfully integrated.

5. **New York Times**: While the New York Times offers an API, it restricts access to article content.

6. **BBC News**: Unfortunately, BBC News has discontinued its public API.

7. **NewsAPI.org**: Duplicate


Alternative Source Selection: Due to the limitations mentioned above, I used The Guardian, NYTimes and ESPN.com:

1. **NYTimes** API (Limited Content): I integrated the NYTimes API to provide basic article information (e.g., titles, summaries).

2. **ESPN.com** API (Unofficial): I identified and integrated a non-public API from ESPN.com to retrieve full article content.   
	Please note: While the ESPN.com API provides functionality, it is not an officially supported service and may require adjustments in the future.

## Configuration

Configure this project with these environment variables before use.

- `APP_KEY`: The application key
- `DB_CONNECTION`: The database connection type
- `DB_HOST`: The database host
- `DB_PORT`: The database port
- `DB_DATABASE`: The database name
- `DB_USERNAME`: The database username
- `DB_PASSWORD`: The database password
- `GARDIAN_API_KEY`: The API key for the Guardian news source
- `NYTIMES_API_KEY`: The API key for the New York Times news source

## License

This project is licensed under the MIT License.
