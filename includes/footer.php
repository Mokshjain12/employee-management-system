<!-- End of main content -->
    </main>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner spinner-lg"></div>
    </div>
    
    <!-- Modals -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal" id="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Modal Title</h3>
                <button class="modal-close" onclick="closeModal('modal-overlay')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modal-body">
                Modal content goes here
            </div>
            <div class="modal-footer" id="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('modal-overlay')">Cancel</button>
                <button class="btn btn-primary" id="modal-confirm">Confirm</button>
            </div>
        </div>
    </div>
    
    <script src="<?php echo APP_URL; ?>/public/js/app.js"></script>
    <script>
        // Toggle user dropdown
        function toggleDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            dropdown.classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('user-dropdown');
            if (userMenu && !userMenu.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>
