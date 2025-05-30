# Backend Mighty

A robust and lightweight API backend built with the Lumen Framework, designed for high-performance microservices and modern web applications. Backend Mighty provides a solid foundation for building scalable RESTful APIs with clean architecture and efficient database operations.

## 📦 Requirements

- **PHP** >= 7.3
- **Composer** – Dependency management
- **Database** – MySQL, PostgreSQL, SQLite, or SQL Server
- **Postman** (optional) – For API testing and documentation

## 🚀 Getting Started

Follow these steps to get Backend Mighty up and running on your local machine:

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/backend-mighty.git
cd backend-mighty
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
```

Edit the `.env` file with your credentials and endpoints:

#### Local Development `.env`

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=https://my-lumen-app.loca.lt

# Passport / OAuth2 (using staging endpoints)
PASSPORT_CLIENT_ID=2
PASSPORT_CLIENT_SECRET=RC0GY1AuQYF5UAuTFBhuETiLhOY03tfsj3ZUbuPN
PASSPORT_LOGIN_ENDPOINT=https://service-staging.mig.id/v1/oauth/token
```

#### Staging Environment `.env.staging`

```dotenv
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://service-staging.mig.id

# Passport / OAuth2 (staging)
PASSPORT_CLIENT_ID=2
PASSPORT_CLIENT_SECRET=RC0GY1AuQYF5UAuTFBhuETiLhOY03tfsj3ZUbuPN
PASSPORT_LOGIN_ENDPOINT=https://service-staging.mig.id/v1/oauth/token
```

### 4. SSL Certificate Setup for Local Development (Windows)

Since the local environment uses HTTPS endpoints for Passport authentication, you need to configure SSL certificates:

1. **Download** the CA bundle:
   [https://curl.se/ca/cacert.pem](https://curl.se/ca/cacert.pem)

2. **Save** it to:
   ```
   C:\tools\php74\extras\ssl\cacert.pem
   ```

3. **Edit** your `php.ini` (e.g. `C:\tools\php74\php.ini`) and add or update:
   ```ini
   [curl]
   curl.cainfo = "C:/tools/php74/extras/ssl/cacert.pem"

   [openssl]
   openssl.cafile = "C:/tools/php74/extras/ssl/cacert.pem"
   ```

4. **Restart** your web server or PHP service.

5. **Verify** the configuration:
   ```bash
   php -i | findstr /C:"curl.cainfo" /C:"openssl.cafile"
   ```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Database Migrations

```bash
php artisan migrate
```

### 7. Start the Development Server

```bash
php -S localhost:8000 -t public
```

Your API will be available at `http://localhost:8000`

## 🧪 Testing the API

### Using cURL

Test if the API is running:

```bash
curl -X GET http://localhost:8000/api/health
```

Example API request:

```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword"
  }'
```

### Using Postman

1. Import the API collection (if available in `/docs/postman`)
2. Set the base URL to `http://localhost:8000` (or your LocalTunnel/ngrok URL)
3. Configure any required headers or authentication tokens
4. Start testing your endpoints

## 📁 Project Structure

```
backend-mighty/
├── .vscode/
├── app/
│   ├── Casts/
│   ├── Console/
│   │   └── Commands/
│   │       └── Exclusive/
│   ├── Events/
│   ├── Exceptions/
│   ├── Exports/
│   ├── Helpers/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Imports/
│   ├── Jobs/
│   ├── Listeners/
│   ├── Mail/
│   ├── Providers/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeds/
├── etc/
│   └── env/
├── public/
│   └── img/
├── resources/
│   └── views/
│       ├── emails/
│       ├── excel/
│       └── pdf/
├── routes/
├── storage/
│   ├── app/
│   ├── framework/
│   │   ├── cache/
│   │   │   └── data/
│   │   └── views/
│   └── logs/
└── tests/
```

## 🛠 Common Artisan Commands

### Database Operations

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Reset and re-run all migrations
php artisan migrate:refresh

# Seed the database
php artisan db:seed
```

### Code Generation

```bash
# Create a new controller
php artisan make:controller UserController

# Create a new model
php artisan make:model Post

# Create a new migration
php artisan make:migration create_posts_table

# Create a new middleware
php artisan make:middleware AuthMiddleware
```

### Cache Management

```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear
```

### Development Tools

```bash
# List all available routes
php artisan route:list

# Run tests
php artisan test

# Generate API documentation (if configured)
php artisan api:docs
```

## 👨‍💻 Contributing

We welcome contributions to Backend Mighty! Please follow these guidelines:

### How to Contribute

1. **Fork the repository** to your GitHub account

2. **Create a feature branch** from the main branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes** and ensure they follow our coding standards

4. **Commit your changes** with descriptive messages:
   ```bash
   git commit -m "Add: new user authentication feature"
   ```

5. **Push to your branch**:
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Submit a Pull Request** with a clear description of your changes

### Code Standards

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed
- Ensure all tests pass before submitting

### Reporting Issues

Please use GitHub Issues to report bugs or request features. Include:

- Clear description of the issue
- Steps to reproduce
- Expected vs actual behavior
- Environment details

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

**Backend Mighty** – Building powerful APIs with simplicity and performance in mind.