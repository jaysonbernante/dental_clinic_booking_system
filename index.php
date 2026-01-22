<?php 
include 'component/index_top.php';
?>

    <main>
        <section id="main" class="hero">
            <div class="container hero-content-wrapper">
                <div class="hero-text reveal">
                    <h1>Your Smile is <br><span>Our Passion</span></h1>
                    <p>Experience gentle, personalized dental care in a calm and comfortable environment. Your journey to a healthier smile starts here.</p>
                    <div class="hero-btns">
                        <a href="login.php" class="btn">Book an Appointment</a>
                        <a href="#services" class="btn btn-outline" style="margin-left: 10px;">Learn More</a>
                    </div>
                </div>
                <div class="hero-image-container reveal delay-200">
                   
                    <div class="floating-card">
                        <div class="icon-box">
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size: 0.9rem;">5.0 Rating</h4>
                            <small style="color:var(--text-light)">200+ Reviews</small>
                        </div>
                    </div>
                    <img src="https://images.pexels.com/photos/6627566/pexels-photo-6627566.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Dentist with a patient smiling" class="hero-image">
                </div>
            </div>
        </section>

        
        <section id="services" class="services">
            <div class="container">
                <div class="section-header reveal">
                    <h2>Our Services</h2>
                    <p>Comprehensive care for the whole family</p>
                </div>
                <div class="services-grid">
                    <div class="service-item reveal delay-100">
                        <div class="icon-container">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <h3>General Checkup</h3>
                        <p>Comprehensive oral exams to keep your smile healthy and detect issues early.</p>
                    </div>
                    <div class="service-item reveal delay-200">
                        <div class="icon-container">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <h3>Teeth Cleaning</h3>
                        <p>Professional cleaning to remove plaque and tartar, leaving your teeth feeling fresh.</p>
                    </div>
                    <div class="service-item reveal delay-300">
                        <div class="icon-container">
                            <i class="fa-regular fa-gem"></i>
                        </div>
                        <h3>Cosmetic Dentistry</h3>
                        <p>Enhance your smile with whitening, veneers, and other aesthetic treatments.</p>
                    </div>
                </div>
            </div>
        </section>

       
        <section id="about">
            <div class="container">
                <div class="about-grid">
                    <div class="about-img-wrapper reveal">
                        <!-- Experience Badge with Pulse Animation -->
                        <div class="experience-badge">
                            <span>10+</span>
                            <small>Years</small>
                        </div>
                        <img src="https://images.pexels.com/photos/5989175/pexels-photo-5989175.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="A friendly female dentist">
                    </div>
                    <div class="about-content reveal delay-200">
                        <h4 style="color: var(--primary-color); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">About Us</h4>
                        <h2>Meet Dr. Peter Neil Furigay Gondayao</h2>
                        <p>Hi, I'm Dr. Peter Neil Furigay Gondayao. I believe that dental care should be a gentle and positive experience. With over 10 years of practice, my focus is on providing personalized care in a friendly, stress-free setting.</p>
                        <p>I'm committed to helping you achieve and maintain a beautiful, healthy smile for life using the latest painless technology.</p>
                        
                        <ul class="features-list">
                            <li><i class="fa-solid fa-check-circle"></i> Painless Technology</li>
                            <li><i class="fa-solid fa-check-circle"></i> Experienced Specialists</li>
                            <li><i class="fa-solid fa-check-circle"></i> Modern Clinic</li>
                            <li><i class="fa-solid fa-check-circle"></i> Emergency Care</li>
                        </ul>
                        <br>
                        <a href="#" class="btn">Read Full Bio</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

   
<?php 
include 'component/index_bottom.php';
?>

</html>