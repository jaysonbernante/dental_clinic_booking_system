<style>
    header {
        background: white;
        padding: 15px 60px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03);
        position: sticky;
        top: 0;
        z-index: 1000;
        
    }

    .logo { color: var(--peter-pink); font-size: 24px; font-weight: 700; text-decoration: none; }
    
    nav { display: flex; gap: 20px; align-items: center; }
    
    nav a { 
        text-decoration: none; 
        color: #666; 
        font-weight: 500; 
        font-size: 14px; 
        transition: 0.3s; 
    }
    
    nav a:hover, nav a.active { color: var(--peter-pink); }

    .btn-appointment {
        background: var(--peter-pink);
        color: white;
        padding: 10px 22px;
        border-radius: 25px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        font-size: 14px;
        transition: 0.3s;
    }
    
    .btn-appointment:hover {
        background: #ff528a;
        box-shadow: 0 4px 12px rgba(255, 107, 157, 0.2);
    }

    /* Bagong Logout Button Style */
    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f0f0f0; /* Light grey background */
        color: #777;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        text-decoration: none;
        transition: 0.3s;
        border: 1px solid #eee;
    }

    .btn-logout:hover {
        background: #ffebee; /* Light pink on hover */
        color: var(--peter-pink);
        border-color: var(--peter-pink);
    }
</style>

<header>
    <a href="#" class="logo">Peter Dental</a>
    <nav>
        <a href="patient_medicalHistory.php"
           class="<?= ($pageTitle === 'patient medicalHistory') ? 'active' : '' ?>">
           Medical History
        </a>
        
        <a href="patient_appointmentStatus.php"
           class="<?= ($pageTitle === 'patient appointmentStatus') ? 'active' : '' ?>">
           Appointment Status
        </a>
  
        <button class="btn-appointment" onclick="openAptModal()">Schedule Appointment</button>

        <a href="../action/logout.php" class="btn-logout" title="Logout">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </nav>
</header>

