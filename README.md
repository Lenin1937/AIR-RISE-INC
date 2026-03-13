# iCorp Pro - Business Formation Platform

A comprehensive Laravel-based business formation platform that helps entrepreneurs start their businesses quickly and efficiently.

## 🚀 Features

- **Business Formation Services**: LLC, C-Corp, S-Corp, Nonprofit, and Green Card services
- **Admin Dashboard**: Complete management system for orders, documents, payments, and users
- **Customer Portal**: Track orders, upload documents, and communicate with support
- **Marketing Website**: Professional landing pages with dark theme (#0b1e33)
- **Payment Integration**: Stripe integration for secure payments
- **Role-based Access**: Admin and customer role management
- **Document Management**: KYC document upload and management
- **Responsive Design**: Mobile-first design with Tailwind CSS

## 🛠️ Tech Stack

- **Backend**: Laravel 11 with PHP 8.2
- **Frontend**: Vue.js 3 with Inertia.js
- **Styling**: Tailwind CSS
- **Database**: PostgreSQL
- **Cache**: File-based caching
- **Build Tools**: Vite
- **Containerization**: Docker & Docker Compose

## 📦 Installation

### Using Docker (Recommended)

1. Clone the repository:
```bash
git clone https://gitlab.com/yasinarafatasif/icorp-pro-website.git
cd icorp-pro-website
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Build and start containers:
```bash
docker-compose up -d --build
```

4. Install dependencies:
```bash
docker exec icorp-pro-app composer install
docker exec icorp-pro-app npm install
```

5. Generate application key:
```bash
docker exec icorp-pro-app php artisan key:generate
```

6. Run migrations:
```bash
docker exec icorp-pro-app php artisan migrate --seed
```

7. Build frontend assets:
```bash
docker exec icorp-pro-app npm run build
```

### Manual Installation

1. Install PHP 8.2+, Node.js 20+, PostgreSQL, and Composer
2. Clone and setup the project:
```bash
git clone https://gitlab.com/yasinarafatasif/icorp-pro-website.git
cd icorp-pro-website
composer install
npm install
cp .env.example .env
php artisan key:generate
```

3. Configure your `.env` file with database credentials
4. Run migrations and build assets:
```bash
php artisan migrate --seed
npm run build
php artisan serve
```

## 🔧 Configuration

Key environment variables:

```env
APP_NAME="iCorp Pro"
APP_URL=http://192.168.0.102:8888

DB_CONNECTION=pgsql
DB_HOST=database
DB_PORT=5432
DB_DATABASE=icorp_pro

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

## 📱 Usage

### Admin Access
- Navigate to `/admin` after logging in with admin credentials
- Manage users, orders, documents, and payments
- View comprehensive dashboard with analytics

### Customer Portal
- Register and login to access customer dashboard
- Create orders for business formation services
- Upload required documents
- Track order progress

### Marketing Site
- Public homepage showcasing services
- Service detail pages (LLC, C-Corp, S-Corp, Nonprofit, Green Card)
- Pricing information
- Contact and about pages

## 🎨 Design System

- **Primary Color**: #0b1e33 (Dark Navy)
- **Accent Color**: #fbbf24 (Yellow)
- **Typography**: Modern sans-serif fonts
- **Layout**: Responsive grid system with Tailwind CSS

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a merge request

## 📝 License

This project is licensed under the MIT License.

## 👨‍💻 Author

**Yasin Arafat Asif**
- GitLab: [@yasinarafatasif](https://gitlab.com/yasinarafatasif)

## 🆘 Support

For support, email support@icorp.pro or create an issue in this repository.

---

Built with ❤️ using Laravel, Vue.js, and modern web technologies.
