<style>
        :root { --peter-pink: #ff6b9d; --peter-blue: #0081C9; --sidebar-width: 260px; --bg-light: #fff5f8; --transition: all 0.3s ease; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--bg-light); display: flex; }

       .sidebar { width: var(--sidebar-width); background-color: var(--peter-pink); height: 100vh; color: white; position: fixed; left: 0; top: 0; z-index: 1000; padding-top: 30px; }
        .sidebar-header { text-align: center; margin-bottom: 30px; }
        .logo-circle { width: 80px; height: 80px; background: white; border-radius: 50%; margin: 0 auto 10px; overflow: hidden; border: 3px solid rgba(255, 255, 255, 0.3); }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        .nav-menu { list-style: none; padding: 0; }
        .nav-item { padding: 15px 25px; display: flex; align-items: center; gap: 12px; color: white; text-decoration: none; font-size: 14px; transition: var(--transition); }
        .nav-item:hover, .nav-item.active { background: rgba(255, 255, 255, 0.2); border-left: 4px solid white; }
</style>

<nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo-circle"><img src="../assets/brand/logo.JPG"></div>
            <h3 style="margin:0;">Peter Dental</h3>
        </div>
        <div class="nav-menu">
            <a href="dentist_dashboard.php" class="nav-item "><i class="fa-solid fa-house"></i> Home</a>
            <a href="dentist_patient.php" class="nav-item"><i class="fa-solid fa-user-group"></i> Patients</a>
            <a href="dentist_appointments.php" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Appointments</a>
            <a href="dentist_Medical_Records.php" class="nav-item"><i class="fa-solid fa-clipboard-list"></i> Medical Questions</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-gear"></i> Admin Settings</a>
        </div>
    </nav>