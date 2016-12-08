<div id="webprofiler-<?php echo $token ?>" class="webprofiler">
    <div class="webprofiler-header">
        <?php
            if (count($tabs) !== 0) {
        ?>
        <div class="webprofiler-tabs">
            <a class="hide-button" title="Close WebProfiler" tabindex="-1" accesskey="D"><?php echo file_get_contents(__DIR__ . '/../icons/ic_clear_white_24px.svg') ?></a>
        <?php
                foreach ($tabs as $name => $tab) {
                    $tooltip = false;

                    if (isset($tab['tooltip'])) {
                        $tooltip = true;
                    }
        ?>
            <a <?php echo (isset($panels[$name]) ? 'href="#webprofiler-panel-' . $name . '"' : ''); ?> class="webprofiler-tab webprofiler-tab-<?php echo $name ?> webprofiler-tab-position-<?php echo $tab['position']?> <?php echo (isset($panels[$name]) ? 'webprofiler-tab-has-panel' : ''); ?><?php if ($tooltip) { ?> webprofiler-tab-has-tooltip<?php } ?><?php if (isset($tab['tab']['class'])) { echo $tab['tab']['class']; } ?>">
                <div class="webprofiler-tab-menu">
                    <?php if (isset($tab['tab']['icon'])) { ?>
                    <span class="webprofiler-tab-icon">
                        <?php echo $tab['tab']['icon'] ?>
                    </span>
                    <?php } ?>
                    <?php if (isset($tab['tab']['status'])) { ?>
                    <span class="webprofiler-tab-status">
                        <?php echo $tab['tab']['status'] ?>
                    </span>
                    <?php } ?>
                    <span class="webprofiler-tab-label">
                        <?php echo $tab['tab']['label'] ?>
                    </span>
                    <span class="webprofiler-tab-value">
                        <?php echo $tab['tab']['value'] ?>
                    </span>
                </div>
                <?php
                    if ($tooltip) {
                ?>
                    <div class="webprofiler-tab-tooltip">
                        <?php echo $tab['tooltip'] ?>
                    </div>
                <?php
                    }
                ?>
            </a>
        <?php
                }
        ?>
        </div>
        <?php
            }
        ?>
    </div>
    <div class="webprofiler-body">
        <?php
        foreach ($panels as $name => $panel) {
        ?>
            <div id="webprofiler-panel-<?php echo $name ?>" class="webprofiler-panel">
                <?php echo $panel ?>
            </div>
        <?php
        }
        ?>
    </div>
</div>
