<?php
$totalRecords = total_records('OaiPmhStaticRepository');
$pageTitle = __('OAI-PMH Static Repositories (%d total)', $total_results);
queue_css_file('oai-pmh-static-repository');
queue_js_file('oai-pmh-static-repository-browse');
if (plugin_is_active('OaiPmhGateway')) {
    queue_css_file('oai-pmh-gateway');
    queue_js_file('oai-pmh-gateway');
    queue_js_file('oai-pmh-gateway-browse');
}
echo head(array(
    'title' => $pageTitle,
    'bodyclass' => 'oai-pmh-static-repository browse',
));
?>
<div id="primary">
    <?php if (is_allowed('OaiPmhStaticRepository_Index', 'add')): ?>
    <div class="right">
        <a href="<?php echo html_escape(url('oai-pmh-static-repository/index/add')); ?>" class="add button small green"><?php echo __('Create a new OAI-PMH Static Repository'); ?></a>
    </div>
    <?php endif; ?>
    <h2><?php echo __('Status of Static Repositories'); ?></h2>
    <?php echo flash(); ?>
<?php if (iterator_count(loop('OaiPmhStaticRepository'))): ?>
    <form action="<?php echo html_escape(url('oai-pmh-static-repository/index/batch-edit')); ?>" method="post" accept-charset="utf-8">
        <div class="table-actions batch-edit-option">
            <?php if (is_allowed('OaiPmhStaticRepository_Index', 'edit')): ?>
            <input type="submit" class="small green batch-action button" name="submit-batch-check" value="<?php echo __('Check'); ?>">
            <input type="submit" class="small green batch-action button" name="submit-batch-update" value="<?php echo __('Update'); ?>">
            <?php endif; ?>
            <?php
                $actionUri = $this->url(array(
                        'action' => 'browse',
                    ),
                    'default');
                $action = __('Refresh page');
                ?>
            <a href="<?php echo html_escape($actionUri); ?>" class="refresh button blue"><?php echo $action; ?></a>
            <?php if (is_allowed('OaiPmhStaticRepository_Index', 'delete')): ?>
            <input type="submit" class="small red batch-actiorran button" name="submit-batch-delete" value="<?php echo __('Delete'); ?>">
            <?php endif; ?>
        </div>
        <?php echo common('quick-filters'); ?>
        <div class="pagination"><?php echo $paginationLinks = pagination_links(); ?></div>
        <table id="oai-pmh-static-repositories">
            <thead>
                <tr>
                    <?php if (is_allowed('OaiPmhStaticRepository_Index', 'edit')): ?>
                    <th class="batch-edit-heading"><?php // echo __('Select'); ?></th>
                    <?php endif;
                    $browseHeadings[__('Folder')] = 'uri';
                    $browseHeadings[__('Folder and OAI-PMH Status')] = 'status';
                    $browseHeadings[__('Action')] = null;
                    $browseHeadings[__('Last Modified')] = 'modified';
                    echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => ''));
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php $key = 0; ?>
                <?php foreach (loop('OaiPmhStaticRepository') as $folder):
                    $gateway = $folder->getGateway();
                    $harvest = $gateway ? $gateway->getHarvest($folder->getParameter('oaipmh_harvest_prefix')) : null;
                ?>
                <tr class="oai-pmh-static-repository <?php if (++$key%2 == 1) echo 'odd'; else echo 'even'; ?>">
                    <?php if (is_allowed('OaiPmhGateway_Index', 'edit')): ?>
                    <td class="batch-edit-check" scope="row">
                        <input type="checkbox" name="folders[]" value="<?php echo $folder->id; ?>" />
                    </td>
                    <?php endif; ?>
                    <td><?php
                        echo html_escape($folder->uri); ?>
                        <br />
                        <?php if (empty($folder->messages)): ?>
                            <div class="details">
                                <?php  echo __('No message'); ?>
                            </div>
                        <?php else: ?>
                            <ul class="action-links group">
                                <li>
                                   <a href="<?php echo ADMIN_BASE_URL; ?>" id="oai-pmh-static-repository-<?php echo $folder->id; ?>" class="oai-pmh-static-repository-details"><?php echo __('Last Messages'); ?></a>
                                </li>
                                <li>
                                    <a href="<?php echo ADMIN_BASE_URL . '/oai-pmh-static-repository/index/logs/id/' . $folder->id; ?>"><?php echo __('All Messages'); ?></a>
                                </li>
                            </ul>
                            <div class="details" style="display: none;">
                                <?php  echo nl2br(str_replace(']', "]\n", substr($folder->messages, -400))); ?>
                            </div>
                            <div class="last-message" style="display: auto;">
                                <?php
                                    $pos = strrpos($folder->messages, PHP_EOL . '[');
                                    echo $pos ? nl2br(substr($folder->messages, $pos + 1)) : $folder->messages;
                                ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                            echo common('oai-pmh-static-repository-status', array('folder' => $folder));
                        ?>
                        <p>
                            <em>
                        <?php if ($gateway && $folder->status != OaiPmhStaticRepository::STATUS_ADDED): ?>
                            <a href="<?php echo html_escape($gateway->getBaseUrl() . '?verb=Identify'); ?>" target="_blank"><?php echo __('OAI-PMH Gateway'); ?></a>
                        <?php else: ?>
                            <?php echo __('OAI-PMH Gateway'); ?>
                        <?php endif; ?>
                            </em>
                        </p>
                        <?php if ($gateway && $folder->status != OaiPmhStaticRepository::STATUS_ADDED):
                            echo common('gateway-public', array('gateway' => $gateway, 'asText' => false, 'textPublic' => true));
                            echo common('gateway-status', array('gateway' => $gateway, 'asText' => false));
                            echo common('gateway-friend', array('gateway' => $gateway, 'asText' => false, 'textFriend' => true));
                        else:
                            echo __('No gateway');
                        endif;
                        ?>
                        <p>
                            <em>
                                <?php echo __('OAI-PMH Harvester'); ?>
                            </em>
                        </p>
                        <?php if ($harvest && $folder->status != OaiPmhStaticRepository::STATUS_ADDED):
                            echo common('harvest-status', array('harvest' => $harvest, 'asText' => true)); ?>
                            <div class="harvest-full-status">
                                <?php echo '<div class="harvest-prefix">[' . $harvest->metadata_prefix . ']</div>'; ?>
                                <a href="<?php echo url('oaipmh-harvester/index/status', array('harvest_id' => $harvest->id)); ?>"><?php echo __('Full Status'); ?></a>
                            </div>
                        <?php else:
                            echo __('None');
                        endif;
                        ?>
                    </td>
                    <td>
                    <?php
                        switch ($folder->status):
                            case OaiPmhStaticRepository::STATUS_QUEUED:
                            case OaiPmhStaticRepository::STATUS_PROGRESS:
                                $actionUri = $this->url(array(
                                        'action' => 'stop',
                                        'id' => $folder->id,
                                    ),
                                    'default');
                                $action = __('Stop');
                                ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="stop button blue"><?php echo $action; ?></a>

                            <?php
                                $actionUri = $this->url(array(
                                        'action' => 'browse',
                                    ),
                                    'default');
                                $action = __('Refresh page');
                                ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="refresh button blue"><?php echo $action; ?></a>
                            <?php break;
                            case OaiPmhStaticRepository::STATUS_ADDED:
                            case OaiPmhStaticRepository::STATUS_RESET:
                            case OaiPmhStaticRepository::STATUS_PAUSED:
                            case OaiPmhStaticRepository::STATUS_STOPPED:
                            case OaiPmhStaticRepository::STATUS_KILLED:
                            case OaiPmhStaticRepository::STATUS_COMPLETED:
                            case OaiPmhStaticRepository::STATUS_DELETED:
                            case OaiPmhStaticRepository::STATUS_ERROR:
                            default:

                                 if (is_allowed('OaiPmhStaticRepository_Index', 'edit')):
                                    $actionUri = $this->url(array(
                                            'action' => 'check',
                                            'id' => $folder->id,
                                        ),
                                        'default');
                                    $action = __('Check');
                        ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="check button green"><?php echo $action; ?></a>
                        <?php
                                    $actionUri = $this->url(array(
                                            'action' => 'update',
                                            'id' => $folder->id,
                                        ),
                                        'default');
                                    $action = __('Update');
                        ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="update button green"><?php echo $action; ?></a>
                        <?php

                                    if (!in_array($folder->status, array(OaiPmhStaticRepository::STATUS_ADDED, OaiPmhStaticRepository::STATUS_COMPLETED))):
                                        $actionUri = $this->url(array(
                                                'action' => 'reset-status',
                                                'id' => $folder->id,
                                            ),
                                            'default');
                                        $action = __('Reset status'); ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="reset-status button green"><?php echo $action; ?></a>
                                    <?php endif;
                                endif;

                                if ($gateway && $folder->status != OaiPmhStaticRepository::STATUS_ADDED):
                                    $actionUri = $this->url(array(
                                            'module' => 'oai-pmh-gateway',
                                            'controller' => 'index',
                                            'action' => 'check',
                                            'id' => $gateway->id,
                                        ),
                                        'default');
                                    $action = __('Check gateway'); ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="harvest button blue"><?php echo $action; ?></a>
                                <?php
                                elseif ($folder->status != OaiPmhStaticRepository::STATUS_ADDED):
                                    $actionUri = $this->url(array(
                                            'module' => 'oai-pmh-static-repository',
                                            'controller' => 'index',
                                            'action' => 'create-gateway',
                                            'id' => $folder->id,
                                        ),
                                        'default');
                                    $action = __('Create gateway'); ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="harvest button blue"><?php echo $action; ?></a>
                                <?php endif;

                                if ($folder->isSetToBeHarvested() && $folder->status != OaiPmhStaticRepository::STATUS_ADDED):
                                    if ($harvest and in_array($harvest->status, array(OaipmhHarvester_Harvest::STATUS_QUEUED, OaipmhHarvester_Harvest::STATUS_IN_PROGRESS))):
                                $actionUri = $this->url(array(
                                        'action' => 'browse',
                                    ),
                                    'default');
                                $action = __('Refresh page');
                                ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="refresh button blue"><?php echo $action; ?></a>
                                <?php else:
                                    $actionUri = $this->url(array(
                                            'action' => 'harvest',
                                            'id' => $folder->id,
                                        ),
                                        'default');
                                    $action = __('Harvest'); ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="harvest button blue"><?php echo $action; ?></a>
                                <?php endif;
                                endif;

                                if (is_allowed('OaiPmhStaticRepository_Index', 'delete')):
                                    $actionUri = $this->url(array(
                                            'action' => 'delete-confirm',
                                            'id' => $folder->id,
                                        ),
                                        'default');
                                    $action = __('Delete'); ?>
                        <a href="<?php echo html_escape($actionUri); ?>" class="delete-confirm button red"><?php echo $action; ?></a>
                                <?php endif;

                                break;
                        endswitch;
                    ?>
                    </td>
                    <td><?php echo html_escape(format_date($folder->modified, Zend_Date::DATETIME_SHORT)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (plugin_is_active('OaiPmhGateway')): ?>
        <p style="text-align: right;"><em><?php echo __('Click public, status or friend to switch it.'); ?></em></p>
        <?php endif; ?>
        <div class="pagination"><?php echo $paginationLinks; ?></div>
    </form>
    <script type="text/javascript">
        Omeka.messages = jQuery.extend(Omeka.messages,
            {'oaiPmhStaticRepository':{
                'confirmation':<?php echo json_encode(__('Are your sure to remove these folders?')); ?>
        <?php if (plugin_is_active('OaiPmhGateway')): ?>
            },
            'oaiPmhGateway':{
                'public':<?php echo json_encode(__('Public')); ?>,
                'notPublic':<?php echo json_encode(__('Reserved')); ?>,
                'initiated':<?php echo json_encode(__('Initiated')); ?>,
                'terminated':<?php echo json_encode(__('Terminated')); ?>,
                'friend':<?php echo json_encode(__('Friend')); ?>,
                'notFriend':<?php echo json_encode(__('Not friend')); ?>,
                'undefined':<?php echo json_encode(__('Undefined')); ?>,
                'checkGood':<?php echo json_encode(__('Checked good')); ?>,
                'checkError':<?php echo json_encode(__('Checked error')); ?>,
                'confirmation':<?php echo json_encode(__('Are your sure to remove these gateways?')); ?>
        <?php endif; ?>
            }}
        );
        Omeka.addReadyCallback(Omeka.OaiPmhStaticRepositoryBrowse.setupBatchEdit);
    </script>
<?php else: ?>
    <?php if ($totalRecords): ?>
        <p><?php echo __('The query searched %s records and returned no results.', $totalRecords); ?></p>
        <p><a href="<?php echo url('oai-pmh-static-repository/index/browse'); ?>"><?php echo __('See all folders.'); ?></a></p>
    <?php else: ?>
        <p><?php echo __('No url or path have been checked or exposed.'); ?></p>
    <?php endif; ?>
<?php endif; ?>
</div>
<?php
    echo foot();
