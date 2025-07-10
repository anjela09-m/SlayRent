<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($_GET['success']); ?>
        <span class="close-btn">&times;</span>
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <?php echo htmlspecialchars($_GET['error']); ?>
        <span class="close-btn">&times;</span>
    </div>
<?php endif; ?>