<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>
        Malaika Beauty Parlor & Boutique
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
    >

    <style>

        :root {
            --malaika-green: #1a936f;
            --malaika-dark: #1a1a2e;
            --malaika-gold: #f4a261;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
        }

        .navbar-logo {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .btn-malaika {
            background: var(--malaika-green);
            color: white;
            border: none;
        }

        .btn-malaika:hover {
            background: #147a5c;
            color: white;
        }

        .text-malaika {
            color: var(--malaika-green) !important;
        }

        .bg-malaika {
            background: var(--malaika-green) !important;
        }

        .hero-section {
            background: linear-gradient(
                135deg,
                #1a936f 0%,
                #114b5f 100%
            );

            color: white;
            padding: 80px 0;
        }

        .home-logo {
            width: 180px;
            height: 180px;
            object-fit: contain;
            max-width: 100%;
        }

        .auth-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            max-width: 100%;
        }

        .card-hover {
            transition: transform 0.2s;
        }

        .card-hover:hover {
            transform: translateY(-5px);
        }

        .footer {
            background: var(--malaika-dark);
            color: #aaa;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .nav-link {
            font-weight: 500;
        }

        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {

            .home-logo {
                width: 130px;
                height: 130px;
            }

            .navbar-logo {
                width: 40px;
                height: 40px;
            }

        }

    </style>

</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-malaika sticky-top">

    <div class="container">

        <!-- WEBSITE LOGO AND NAME -->

        <a
            class="navbar-brand d-flex align-items-center"
            href="<?php echo BASE_URL; ?>index.php"
        >

            <img
                src="<?php echo BASE_URL; ?>images/logo.png"
                alt="Malaika Logo"
                class="navbar-logo me-2"
            >

            <span>MALAIKA</span>

        </a>


        <!-- MOBILE MENU BUTTON -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
        >
            <span class="navbar-toggler-icon"></span>
        </button>


        <div
            class="collapse navbar-collapse"
            id="mainNav"
        >

            <!-- LEFT MENU -->

            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="<?php echo BASE_URL; ?>index.php"
                    >
                        Home
                    </a>
                </li>


                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="<?php echo BASE_URL; ?>services.php"
                    >
                        Services
                    </a>
                </li>


                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="<?php echo BASE_URL; ?>catalog.php"
                    >
                        Boutique
                    </a>
                </li>


                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="<?php echo BASE_URL; ?>about.php"
                    >
                        About
                    </a>
                </li>


                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="<?php echo BASE_URL; ?>cart.php"
                    >
                        <i class="bi bi-cart3"></i>
                        Cart
                    </a>

                </li>

            </ul>


            <!-- RIGHT MENU -->

            <ul class="navbar-nav">

                <?php if (isLoggedIn()): ?>

                    <li class="nav-item dropdown">

                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            data-bs-toggle="dropdown"
                        >

                            <i class="bi bi-person-circle"></i>

                            <?php
                            echo htmlspecialchars(
                                $_SESSION['full_name']
                            );
                            ?>

                        </a>


                        <ul class="dropdown-menu dropdown-menu-end">

                            <?php if (hasRole('Admin')): ?>

                                <li>

                                    <a
                                        class="dropdown-item"
                                        href="<?php echo BASE_URL; ?>admin/dashboard.php"
                                    >

                                        <i class="bi bi-speedometer2"></i>

                                        Admin Dashboard

                                    </a>

                                </li>

                            <?php elseif (hasRole('Staff')): ?>

                                <li>

                                    <a
                                        class="dropdown-item"
                                        href="<?php echo BASE_URL; ?>staff/dashboard.php"
                                    >

                                        <i class="bi bi-calendar-check"></i>

                                        Staff Dashboard

                                    </a>

                                </li>

                            <?php else: ?>

                                <li>

                                    <a
                                        class="dropdown-item"
                                        href="<?php echo BASE_URL; ?>client/dashboard.php"
                                    >

                                        <i class="bi bi-person"></i>

                                        My Account

                                    </a>

                                </li>

                            <?php endif; ?>


                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            <li>

                                <a
                                    class="dropdown-item text-danger"
                                    href="<?php echo BASE_URL; ?>logout.php"
                                >

                                    <i class="bi bi-box-arrow-right"></i>

                                    Logout

                                </a>

                            </li>

                        </ul>

                    </li>


                <?php else: ?>


                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="<?php echo BASE_URL; ?>login.php"
                        >
                            Login
                        </a>

                    </li>


                    <li class="nav-item">

                        <a
                            class="btn btn-light btn-sm ms-2 mt-1"
                            href="<?php echo BASE_URL; ?>register.php"
                        >
                            Register
                        </a>

                    </li>


                <?php endif; ?>

            </ul>

        </div>

    </div>

</nav>


<!-- FLASH MESSAGES -->

<?php
$flash = getFlash();

if ($flash):
?>

<div class="container mt-3">

    <div
        class="alert alert-<?php echo $flash['type']; ?>
        alert-dismissible fade show"
    >

        <?php echo $flash['message']; ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    </div>

</div>

<?php endif; ?>