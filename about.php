<?php
require_once 'includes/config.php';
$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row align-items-center g-5">
        <div class="col-lg-6">
            <h2 class="fw-bold mb-4">About Malaika</h2>
            <p class="lead">Malaika Beauty Parlor & Boutique is your one-stop destination for premium beauty services and curated fashion.</p>
            <p>Owned by Ms. LMJ Mogapi and operated with the assistance of Boitumelo, we offer professional beauty treatments including Nails, Eyelashes, and Massages. Our boutique features stylish clothing for Women, Men, and Kids.</p>
            <p>With our new online platform, you can now book appointments and browse our collection from the comfort of your home.</p>
            <div class="mt-4">
                <a href="services.php" class="btn btn-malaika me-2">Our Services</a>
                <a href="catalog.php" class="btn btn-outline-dark">Shop Now</a>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">Business Hours</h5>
                <ul class="list-unstyled">
                    <li class="d-flex justify-content-between py-2 border-bottom"><span>Monday – Friday</span> <strong>09:00 – 18:00</strong></li>
                    <li class="d-flex justify-content-between py-2 border-bottom"><span>Saturday</span> <strong>09:00 – 16:00</strong></li>
                    <li class="d-flex justify-content-between py-2"><span>Sunday</span> <strong>Closed</strong></li>
                </ul>
                <h5 class="fw-bold mt-4 mb-3">Contact Us</h5>
                <p class="mb-1"><i class="bi bi-telephone text-malaika"></i> 073 456 7890</p>
                <p class="mb-1"><i class="bi bi-envelope text-malaika"></i> info@malaika.co.za</p>
                <p><i class="bi bi-geo-alt text-malaika"></i> South Africa</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
