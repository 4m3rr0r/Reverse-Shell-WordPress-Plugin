<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. System Information & OS Detection
 */
$is_windows   = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
$current_user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user();
$hostname     = gethostname();


$symbol       = $is_windows ? ' >' : ':~$';
$real_prompt  = htmlspecialchars($current_user . "@" . $hostname . $symbol);


$rswp_nonce = wp_create_nonce('rswp_web_nonce');
?>

<h1 class="text-center mt-4 mb-3 text-red">WEB TERMINAL</h1>

<div class="d-flex justify-content-center align-items-center full-center">
  
  <div class="terminal-box" style="background: #000; color: #fff; padding: 15px; border-radius: 5px; width: 100%; max-width: 900px;">

    <div id="v-out" style="height: 400px; overflow-y: auto; font-family: 'Courier New', Courier, monospace;">
        <div class="text-red font-weight-bold">RSWP TERMINAL CONSOLE [OS: <?php echo PHP_OS; ?>]</div>
        <hr class="border-secondary">
    </div>

    <div class="input-group mt-3">
      <span class="input-group-text bg-dark text-white border-secondary"><?php echo $real_prompt; ?></span>
      <input type="text" id="rswp-terminal-in" class="form-control bg-dark text-white border-secondary" placeholder="type command..." autofocus autocomplete="off">
    </div>

  </div>

</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    const $terminalInput = $('#rswp-terminal-in');
    const $terminalOutput = $('#v-out');
    const promptText = '<?php echo $real_prompt; ?>';

    $terminalInput.on('keypress', function(e) {
        if (e.which === 13) { 
            const cmd = $(this).val();
            if (cmd.trim() === '') return;

            // Display command in terminal
            $terminalOutput.append('<div class="mt-2"><span class="text-red font-weight-bold">' + promptText + '</span> <span class="text-white"> ' + cmd + '</span></div>');
            
            $(this).val('');

            // Execute via AJAX
            $.post(ajaxurl, {
                action: 'rswp_web_exec',
                cmd: cmd,
                security: '<?php echo $rswp_nonce; ?>'
            }, function(response) {
                // If response is empty, show a generic success or newline
                const output = response ? response : "[Command executed with no output]";
                $terminalOutput.append('<pre class="text-success border-0 bg-transparent p-0 m-0" style="white-space: pre-wrap;">' + output + '</pre>');
                
                // Auto-scroll to bottom
                $terminalOutput.scrollTop($terminalOutput[0].scrollHeight);
            }).fail(function() {
                $terminalOutput.append('<div class="text-warning">[!] CONNECTION_LOST or PERMISSION_DENIED</div>');
            });
        }
    });
});
</script>