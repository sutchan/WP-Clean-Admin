jQuery(document).ready(function($) {
    $(document).on('click', '.wpca-run-diagnostics', function(e) {
        e.preventDefault();
        
        var $widget = $(this).closest('.wpca-diagnostics-widget');
        var $placeholder = $widget.find('.wpca-diagnostics-placeholder');
        var $loading = $widget.find('.wpca-diagnostics-loading');
        var $results = $widget.find('.wpca-diagnostics-results');
        
        $placeholder.hide();
        $loading.show();
        $results.hide();
        
        $.ajax({
            url: wpca_dashboard_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wpca_run_diagnostics',
                _wpnonce: wpca_dashboard_vars.nonce
            },
            dataType: 'json',
            success: function(response) {
                $loading.hide();
                $results.show();
                
                if (response.success && response.data) {
                    var data = response.data;
                    var summary = data.summary;
                    var checks = data.data;
                    
                    var statusText = '';
                    var statusClass = '';
                    
                    if (data.status === 'error') {
                        statusText = 'Error';
                        statusClass = 'wpca-status-error';
                    } else if (data.status === 'warning') {
                        statusText = 'Warning';
                        statusClass = 'wpca-status-warning';
                    } else {
                        statusText = 'All Good';
                        statusClass = 'wpca-status-success';
                    }
                    
                    $widget.find('.wpca-diagnostics-status').html('<strong class="' + statusClass + '">' + statusText + '</strong>');
                    $widget.find('.wpca-diagnostics-count').html(
                        summary.passed + ' passed, ' + 
                        summary.warning + ' warnings, ' + 
                        summary.error + ' errors'
                    );
                    
                    var listHtml = '<ul>';
                    $.each(checks, function(index, check) {
                        var statusIcon = '';
                        var itemClass = '';
                        
                        switch(check.status) {
                            case 'pass':
                                statusIcon = '✓';
                                itemClass = 'wpca-check-pass';
                                break;
                            case 'warning':
                                statusIcon = '⚠';
                                itemClass = 'wpca-check-warning';
                                break;
                            case 'error':
                                statusIcon = '✗';
                                itemClass = 'wpca-check-error';
                                break;
                        }
                        
                        listHtml += '<li class="' + itemClass + '">';
                        listHtml += '<span class="wpca-status-icon">' + statusIcon + '</span>';
                        listHtml += '<span class="wpca-check-name">' + check.name + '</span>';
                        listHtml += '<span class="wpca-check-message">' + check.message + '</span>';
                        if (check.action) {
                            listHtml += '<span class="wpca-check-action">' + check.action + '</span>';
                        }
                        listHtml += '</li>';
                    });
                    listHtml += '</ul>';
                    
                    $widget.find('.wpca-diagnostics-list').html(listHtml);
                }
            },
            error: function() {
                $loading.hide();
                $placeholder.show();
                alert('Failed to run diagnostics');
            }
        });
    });
    
    $(document).on('click', '.wpca-quick-action', function(e) {
        e.preventDefault();
        
        var action = $(this).data('action');
        var $button = $(this);
        var originalText = $button.val();
        
        $button.val('Processing...').prop('disabled', true);
        
        $.ajax({
            url: wpca_dashboard_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'wpca_run_quick_action',
                _wpnonce: wpca_dashboard_vars.nonce,
                action_name: action
            },
            dataType: 'json',
            success: function(response) {
                $button.val(originalText).prop('disabled', false);
                
                if (response.success && response.data) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Action failed');
                }
            },
            error: function() {
                $button.val(originalText).prop('disabled', false);
                alert('Action failed');
            }
        });
    });
});