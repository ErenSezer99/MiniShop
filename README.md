# MiniShop - E-Commerce Platform

A comprehensive e-commerce platform built with PHP, PostgreSQL, and Tailwind CSS.

## Table of Contents
- [Features](#features)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)
- [Search Functionality](#search-functionality)
- [Contributing](#contributing)
- [License](#license)

## Features

- User authentication (registration, login, logout)
- Product management (CRUD operations)
- Category management
- Shopping cart functionality
- Wishlist functionality
- Order management
- Order history for users
- Admin dashboard with statistics
- Real-time search functionality across multiple entities
- Responsive design using Tailwind CSS
- Session and localStorage management for "Remember Me" functionality

## Project Structure

```
MiniShop/
├── account/
│   └── orders_history.php          # User order history
├── admin/
│   ├── categories/
│   │   ├── categories.php          # Category management
│   │   └── search_categories.php   # Category search endpoint
│   ├── index.php                   # Admin dashboard
│   ├── orders/
│   │   ├── delete_order.php        # Order deletion
│   │   ├── edit_order.php          # Order editing
│   │   ├── orders.php              # Order management
│   │   └── search_orders.php       # Order search endpoint
│   ├── products/
│   │   ├── delete_product.php      # Product deletion
│   │   ├── edit_product.php        # Product editing
│   │   ├── products.php            # Product management
│   │   └── search_products.php     # Product search endpoint
│   ├── users/
│   │   ├── delete_user.php         # User deletion
│   │   └── users.php               # User management
├── assets/
│   ├── css/
│   │   └── style.css               # Custom styles
│   └── js/
│       ├── admin-categories-search.js  # Category search JS
│       ├── admin-orders-search.js      # Order search JS
│       ├── admin-products-search.js    # Product search JS
│       ├── cart.js                 # Cart functionality
│       ├── main.js                 # Main JavaScript functions
│       ├── products-search.js      # Frontend product search
│       └── wishlist.js             # Wishlist functionality
├── auth/
│   ├── login.php                   # User login
│   ├── logout.php                  # User logout
│   └── register.php                # User registration
├── cart/
│   ├── add_to_cart.php             # Add item to cart
│   ├── cart.php                    # View cart
│   ├── checkout.php                # Checkout process
│   ├── get_cart_count.php          # Get cart item count
│   ├── remove_from_cart.php        # Remove item from cart
│   └── update_cart.php             # Update cart quantities
├── config/
│   ├── config.php                  # General configuration
│   └── database.php                # Database connection
├── includes/
│   ├── footer.php                  # Page footer
│   ├── functions.php               # Utility functions
│   └── header.php                  # Page header/navigation
├── products/
│   ├── index.php                   # Product listing
│   └── search_products.php         # Product search endpoint
├── wishlist/
│   ├── add.php                     # Add to wishlist
│   ├── index.php                   # View wishlist
│   └── remove.php                  # Remove from wishlist
└── uploads/                        # Uploaded images directory
```

## Database Schema

### Users Table
```sql
CREATE TABLE public.users (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    username varchar(50) NOT NULL,
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    role varchar(20) DEFAULT 'user'::character varying NOT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP NULL,
    CONSTRAINT users_email_key UNIQUE (email),
    CONSTRAINT users_pkey PRIMARY KEY (id)
);
```

### Categories Table
```sql
CREATE TABLE public.categories (
    id serial4 NOT NULL,
    name varchar(100) NOT NULL,
    description text NULL,
    image varchar(255) NULL,
    CONSTRAINT categories_pkey PRIMARY KEY (id)
);
```

### Products Table
```sql
CREATE TABLE public.products (
    id serial4 NOT NULL,
    name varchar(150) NOT NULL,
    description text NULL,
    price numeric(10, 2) NOT NULL,
    stock int4 DEFAULT 0 NOT NULL,
    category_id int4 NULL,
    image varchar(255) NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP NULL,
    CONSTRAINT products_pkey PRIMARY KEY (id)
);

-- Foreign Keys
ALTER TABLE public.products ADD CONSTRAINT products_category_id_fkey 
    FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE SET NULL;
```

### Cart Table
```sql
CREATE TABLE public.cart (
    id serial4 NOT NULL,
    user_id uuid NULL,
    product_id int4 NULL,
    quantity int4 DEFAULT 1 NOT NULL,
    CONSTRAINT cart_pkey PRIMARY KEY (id)
);

-- Foreign Keys
ALTER TABLE public.cart ADD CONSTRAINT cart_product_id_fkey 
    FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;
ALTER TABLE public.cart ADD CONSTRAINT cart_user_id_fkey 
    FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;
```

### Wishlist Table
```sql
CREATE TABLE public.wishlist (
    id int4 DEFAULT nextval('wishlists_id_seq'::regclass) NOT NULL,
    user_id uuid NOT NULL,
    product_id int4 NOT NULL,
    created_at timestamp DEFAULT now() NULL,
    CONSTRAINT wishlists_pkey PRIMARY KEY (id),
    CONSTRAINT wishlists_user_id_product_id_key UNIQUE (user_id, product_id)
);

-- Foreign Keys
ALTER TABLE public.wishlist ADD CONSTRAINT wishlists_product_id_fkey 
    FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;
ALTER TABLE public.wishlist ADD CONSTRAINT wishlists_user_id_fkey 
    FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;
```

### Orders Table
```sql
CREATE TABLE public.orders (
    id serial4 NOT NULL,
    user_id uuid NULL,
    total_amount numeric(10, 2) NOT NULL,
    status varchar(20) DEFAULT 'pending'::character varying NOT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP NULL,
    guest_name varchar(255) NULL,
    guest_email varchar(255) NULL,
    guest_address text NULL,
    CONSTRAINT orders_pkey PRIMARY KEY (id)
);

-- Foreign Keys
ALTER TABLE public.orders ADD CONSTRAINT orders_user_id_fkey 
    FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;
```

### Order Items Table
```sql
CREATE TABLE public.order_items (
    id serial4 NOT NULL,
    order_id int4 NULL,
    product_id int4 NULL,
    quantity int4 DEFAULT 1 NOT NULL,
    price numeric(10, 2) NOT NULL,
    CONSTRAINT order_items_pkey PRIMARY KEY (id)
);

-- Foreign Keys
ALTER TABLE public.order_items ADD CONSTRAINT order_items_order_id_fkey 
    FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;
ALTER TABLE public.order_items ADD CONSTRAINT order_items_product_id_fkey 
    FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE SET NULL;
```

## Installation

1. Clone the repository:
   ```bash
   git clone <repository-url>
   ```

2. Place the project in your web server directory (e.g., XAMPP htdocs)

3. Create a PostgreSQL database:
   ```sql
   CREATE DATABASE minishop;
   ```

4. Execute the database schema provided above to create all tables

5. Configure the database connection in `config/database.php`

## Configuration

### Database Configuration
Update `config/database.php` with your PostgreSQL credentials:
```php
<?php
// Database connection settings
$host = "localhost";
$port = "5432";
$dbname = "minishop";
$username = "your_username";
$password = "your_password";
?>
```

## Usage

1. Start your web server (Apache) and PostgreSQL database
2. Navigate to `http://localhost/MiniShop` in your browser
3. Register a new user account
4. Login to access the user features
5. Admin users can access the admin panel at `http://localhost/MiniShop/admin`

### User Roles
- **Regular User**: Can browse products, add to cart, manage wishlist, place orders
- **Admin User**: Has all user privileges plus access to admin panel for managing products, categories, orders, and users

## API Endpoints

### Authentication
- `POST /auth/login.php` - User login
- `POST /auth/register.php` - User registration
- `GET /auth/logout.php` - User logout

### Product Management
- `GET /products/index.php` - List all products
- `POST /products/search_products.php` - Search products (AJAX)

### Cart Operations
- `POST /cart/add_to_cart.php` - Add item to cart
- `GET /cart/cart.php` - View cart
- `POST /cart/remove_from_cart.php` - Remove item from cart
- `POST /cart/update_cart.php` - Update cart quantities
- `GET /cart/get_cart_count.php` - Get cart item count
- `GET /cart/checkout.php` - Checkout process

### Wishlist Operations
- `POST /wishlist/add.php` - Add item to wishlist
- `GET /wishlist/index.php` - View wishlist
- `POST /wishlist/remove.php` - Remove item from wishlist

### Admin Operations
- `GET /admin/index.php` - Admin dashboard
- `GET /admin/products/products.php` - Manage products
- `POST /admin/products/search_products.php` - Search products (Admin)
- `GET /admin/categories/categories.php` - Manage categories
- `POST /admin/categories/search_categories.php` - Search categories (Admin)
- `GET /admin/orders/orders.php` - Manage orders
- `POST /admin/orders/search_orders.php` - Search orders (Admin)
- `GET /admin/users/users.php` - Manage users

## Search Functionality

The application includes real-time search functionality across multiple entities:

### Product Search
- Available on both frontend (`products/index.php`) and admin (`admin/products/products.php`)
- Searches by product name and description
- Includes category filtering in admin panel

### Category Search
- Available in admin panel (`admin/categories/categories.php`)
- Searches by category name and description

### Order Search
- Available in admin panel (`admin/orders/orders.php`)
- Searches by order ID, username, guest name, email, and address

All search features use AJAX for real-time filtering without page reloads. The search functionality is implemented with:
- JavaScript event listeners for input changes
- Debounced API calls to prevent excessive requests
- Dynamic DOM updates with returned HTML content
- Loading spinners for better UX

## Technical Implementation Details

### Authentication System
- Session-based authentication with CSRF protection
- Password hashing using PHP's built-in functions
- Role-based access control (user/admin)

### Shopping Cart
- Supports both authenticated and guest users
- Session-based storage for guests
- Database storage for authenticated users
- Automatic synchronization when logging in

### Wishlist
- User-specific functionality (requires authentication)
- Toggle-based add/remove operations
- Real-time UI updates

### Responsive Design
- Mobile-first approach using Tailwind CSS
- Flexible grid layouts
- Adaptive navigation (desktop vs mobile)

### Error Handling
- Comprehensive input validation
- User-friendly error messages
- Graceful degradation for JavaScript-disabled browsers

## Contributing

1. Fork the repository
2. Create a new branch for your feature
3. Commit your changes
4. Push to your branch
5. Create a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.