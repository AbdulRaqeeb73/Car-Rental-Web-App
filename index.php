<?php include_once 'includes/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1>Welcome to Car Rental Management System</h1>
        <p>Find the perfect car for your journey. We offer a wide range of vehicles at competitive prices.</p>
        <div class="hero-buttons">
            <a href="login.php?type=admin" class="btn btn-primary">Admin Login</a>
            <a href="login.php" class="btn">User Login</a>
            <a href="register.php" class="btn">Register Now</a>
        </div>
    </div>
</section>

<section class="container">
    <h2 class="section-title">Our Services</h2>
    <div class="services">
        <div class="card">
            <i class="fas fa-car fa-3x"></i> <!--This is an icon element using Font Awesome, a popular icon library for web development.-->
            <h3>Wide Selection</h3>
            <p>Choose from our extensive fleet of vehicles to suit any occasion.</p>
        </div>
        <div class="card">
            <i class="fas fa-money-bill-wave fa-3x"></i>
            <h3>Best Prices</h3>
            <p>Competitive rates with no hidden fees. Transparent pricing policy.</p>
        </div>
        <div class="card">
            <i class="fas fa-headset fa-3x"></i>
            <h3>24/7 Support</h3>
            <p>Our customer service team is always available to assist you.</p>
        </div>
    </div>
</section>

<section class="container">
    <h2 class="section-title">How It Works</h2>
    <div class="steps">
        <div class="step">
            <div class="step-number">1</div>
            <h3>Register/Login</h3>
            <p>Create an account or log in to access our services.</p>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <h3>Browse Cars</h3>
            <p>Explore our selection of available vehicles.</p>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <h3>Book Your Car</h3>
            <p>Select your dates and confirm your booking.</p>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <h3>Enjoy Your Ride</h3>
            <p>Pick up your car and enjoy your journey!</p>
        </div>
    </div>
</section>

<style>
.hero {
    background-image: url('assets/images/Main-image.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    padding: 100px 20px;
    text-align: center;
    color: white;
    position: relative;
}

/* This is use for making the black overlay so the text should be more visible */
.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
}

.hero-content {
    position: relative;
    z-index: 1;  /*This puts the message above the overlay*/
    max-width: 800px;
    margin: 0 auto;
}

.hero h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.hero p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

.hero-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.services {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin: 30px 0;
}

.card {
    flex: 1;
    min-width: 300px;
    margin: 15px;
    padding: 30px;
    text-align: center;
    background-color: white;
    border-radius: var(--border-radius); /* Making CSS Variables for Consistancy across the project */
    box-shadow: var(--box-shadow);
}

.card i {
    color: var(--primary-color);
    margin-bottom: 20px;
}

.card h3 {
    margin-bottom: 15px;
}

.steps {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin: 30px 0;
    text-align: center;
}

.step {
    flex: 1;
    min-width: 200px;
    margin: 15px;
    position: relative;
}

.step-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: var(--primary-color);
    color: white;
    border-radius: 50%;
    margin: 0 auto 15px;
    font-weight: bold;
}

/* This is use to make the website responsive */
@media (max-width: 768px) {
    .services, .steps {
        flex-direction: column;
    }
    
    .hero h1 {
        font-size: 2rem;
    }
}
</style>

<?php include_once 'includes/footer.php'; ?> 