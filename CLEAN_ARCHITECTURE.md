# Laravel Clean Architecture Setup

To implement Clean Architecture in this Laravel backend, the directory structure typically separates concerns as follows:

*   **`app/Domain/`**: The core of the business logic. Contains Entities, Value Objects, Domain Exceptions, and Repository Interfaces. This layer depends on *nothing*.
*   **`app/Application/`**: Use cases and application logic. Contains DTOs, Use Case classes (Actions/Interactors), and event listeners. This layer depends only on the Domain.
*   **`app/Infrastructure/`**: Implementations of external concerns. Contains Eloquent Models (which implement Domain repository interfaces), external API clients, Mailers, and caching logic to connect the core business rules to the framework/databases.
*   **`app/Presentation/`**: Entry points to the application. Contains Controllers, Form Requests, View Models, and API Resources.

You can initialize a fresh Laravel backend in this container with:
`docker-compose exec app composer create-project --prefer-dist laravel/laravel .`
...and then map the default directories to the structured folders mapped above.