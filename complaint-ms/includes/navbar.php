<nav class="navbar">
    <div class="logo">
        <a href="<?php echo $base_url; ?>index.php">
            <h2>JUST Complaint System</h2>
        </a>
    </div>
    <div class="user-info" style="display: flex; align-items: center;">
        <button id="theme-toggle" class="btn btn-secondary" style="margin-right: 15px; padding: 5px 10px;">🌙 Dark
            Mode</button>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Welcome,
                <?php echo htmlspecialchars($_SESSION['name']); ?>
            </span>
            <a href="<?php echo $base_url; ?>logout.php" class="btn btn-danger"
                style="margin-left: 10px; padding: 5px 10px; font-size: 0.9rem;">Logout</a>
        <?php else: ?>
            <a href="<?php echo $base_url; ?>login.php" class="btn btn-primary"
                style="margin-left: 10px; padding: 5px 10px; font-size: 0.9rem;">Login</a>
            <a href="<?php echo $base_url; ?>register.php" class="btn btn-success"
                style="margin-left: 10px; padding: 5px 10px; font-size: 0.9rem;">Register</a>
        <?php endif; ?>
    </div>
</nav>