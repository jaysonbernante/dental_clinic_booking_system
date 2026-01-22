<?php 
include 'component/index_top.php';
?>

<body>

    <!-- Header (Same as Index) -->
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <i class="fa-solid fa-tooth"></i>
                    Peter Dental
                </a>
                <div class="nav-links" id="navLinks">
                    <a href="index.php#services">Services</a>
                    <a href="index.php#about">About</a>
                    <a href="testimonials.php" class="active-link">Testimonials</a>
                    <a href="#" class="btn">Book Now</a>
                </div>
                <div class="hamburger" id="hamburger">
                    <i class="fa-solid fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <main>
        
        <!-- Page Hero Section -->
        <section class="page-hero">
            <div class="container">
                <h1>What Our Patients Say</h1>
                <p>Real stories from real smiles.</p>
            </div>
        </section>

        <!-- Testimonials Grid Section -->
        <section class="testimonials-page-section">
            <div class="container">
                <div class="testimonials-grid">
                    
                    <!-- Review 1 -->
                    <div class="testimonial-card reveal delay-100">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p>"I used to be terrified of the dentist, but Serenity Dental changed that. The atmosphere is so calm and Dr. Sharma is incredibly gentle!"</p>
                        <div class="client-info">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Client 1">
                            <div>
                                <h4>Sarah Jenkins</h4>
                                <small>Regular Checkup</small>
                            </div>
                        </div>
                    </div>

                    <!-- Review 2 -->
                    <div class="testimonial-card reveal delay-200">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star-half-stroke"></i>
                        </div>
                        <p>"My kids actually look forward to coming here now! The staff is patient and the clinic is beautifully decorated. Highly recommended."</p>
                        <div class="client-info">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Client 2">
                            <div>
                                <h4>Mark Dela Cruz</h4>
                                <small>Family Dentistry</small>
                            </div>
                        </div>
                    </div>

                    <!-- Review 3 -->
                    <div class="testimonial-card reveal delay-300">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p>"Got my teeth whitened here and the results are amazing. Professional service and very affordable prices for the quality."</p>
                        <div class="client-info">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Client 3">
                            <div>
                                <h4>Elena Rodriguez</h4>
                                <small>Cosmetic</small>
                            </div>
                        </div>
                    </div>

                    <!-- Review 4 -->
                    <div class="testimonial-card reveal delay-100">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p>"The best dental experience I've ever had. The clinic is spotless, the staff is friendly, and the procedure was painless."</p>
                        <div class="client-info">
                            <img src="https://randomuser.me/api/portraits/men/85.jpg" alt="Client 4">
                            <div>
                                <h4>John Smith</h4>
                                <small>Root Canal</small>
                            </div>
                        </div>
                    </div>

                    <!-- Review 5 -->
                    <div class="testimonial-card reveal delay-200">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p>"I love the pink theme! It makes the place feel so welcoming and not clinical at all. Dr. Anya is the best."</p>
                        <div class="client-info">
                            <img src="https://randomuser.me/api/portraits/women/90.jpg" alt="Client 5">
                            <div>
                                <h4>Maria Garcia</h4>
                                <small>Consultation</small>
                            </div>
                        </div>
                    </div>

                     <!-- Review 6 -->
                     <div class="testimonial-card reveal delay-300">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="stars">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p>"Professional, efficient, and caring. They explained everything clearly before my procedure. 10/10 would recommend."</p>
                        <div class="client-info">
                            <img src="https://randomuser.me/api/portraits/men/12.jpg" alt="Client 6">
                            <div>
                                <h4>David Lee</h4>
                                <small>Surgery</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

<?php 
include 'component/index_bottom.php';
?>


    <!-- Script for Mobile Menu (Copy from Index) -->
    <script>
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('nav-active');
            const icon = hamburger.querySelector('i');
            if (navLinks.classList.contains('nav-active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });
        
        // Scroll Animation
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    </script>
</body>
</html>