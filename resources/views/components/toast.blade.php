<div id="toast-container" class="toast-container {{ $position }}">
    <div class="toast-item" 
         data-duration="{{ $duration }}"
         data-dismissible="{{ $dismissible ? 'true' : 'false' }}">
        
        @if($type == 'success')
            <div class="toast-content success">
                <div class="toast-header">
                    <div class="toast-icon success">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="toast-text">
                        <p class="toast-title">Succès</p>
                        <p class="toast-message">{{ $message }}</p>
                    </div>
                    @if($dismissible)
                        <div class="toast-close-container">
                            <button class="toast-close">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="toast-progress success"></div>
            </div>
        @elseif($type == 'error')
            <div class="toast-content error">
                <div class="toast-header">
                    <div class="toast-icon error">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="toast-text">
                        <p class="toast-title">Erreur</p>
                        <p class="toast-message">{{ $message }}</p>
                    </div>
                    @if($dismissible)
                        <div class="toast-close-container">
                            <button class="toast-close">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="toast-progress error"></div>
            </div>
        @elseif($type == 'warning')
            <div class="toast-content warning">
                <div class="toast-header">
                    <div class="toast-icon warning">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="toast-text">
                        <p class="toast-title">Attention</p>
                        <p class="toast-message">{{ $message }}</p>
                    </div>
                    @if($dismissible)
                        <div class="toast-close-container">
                            <button class="toast-close">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="toast-progress warning"></div>
            </div>
        @elseif($type == 'info')
            <div class="toast-content info">
                <div class="toast-header">
                    <div class="toast-icon info">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="toast-text">
                        <p class="toast-title">Information</p>
                        <p class="toast-message">{{ $message }}</p>
                    </div>
                    @if($dismissible)
                        <div class="toast-close-container">
                            <button class="toast-close">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="toast-progress info"></div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toastContainer = document.getElementById('toast-container');
    const toastItem = toastContainer.querySelector('.toast-item');
    const progressBar = toastItem.querySelector('.toast-progress');
    const closeButton = toastItem.querySelector('.toast-close');
    
    const duration = parseInt(toastItem.dataset.duration);
    const dismissible = toastItem.dataset.dismissible === 'true';
    
    // Animation d'entrée
    setTimeout(() => {
        toastItem.classList.add('show');
    }, 100);
    
    // Barre de progression
    if (duration > 0) {
        progressBar.style.width = '100%';
        progressBar.style.transition = `width ${duration}ms linear`;
        
        setTimeout(() => {
            progressBar.style.width = '0%';
        }, 100);
    }
    
    // Fermeture automatique
    if (duration > 0) {
        setTimeout(() => {
            removeToast();
        }, duration);
    }
    
    // Fermeture manuelle
    if (dismissible && closeButton) {
        closeButton.addEventListener('click', removeToast);
    }
    
    function removeToast() {
        toastItem.classList.remove('show');
        setTimeout(() => {
            toastContainer.remove();
        }, 300);
    }
});
</script>