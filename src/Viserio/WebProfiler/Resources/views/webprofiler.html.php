<div id="webprofiler-<?php echo $token ?>" class="webprofiler">
    <div class="webprofiler-header">
        <?php
            if (count($tabs) !== 0) {
        ?>
        <div class="webprofiler-tabs">
        <?php
                foreach ($tabs as $tab) {
                    $tooltip = false;

                    if (isset($tab['tooltip'])) {
                        $tooltip = true;
                    }
        ?>
            <div class="webprofiler-tab webprofiler-tab-<?php echo $tab['name'] ?> webprofiler-tab-positoin-<?php echo $tab['position']?><?php if ($tooltip) {?> webprofiler-tab-has-tooltip<?php }?>">
                <div class="webprofiler-tab-icon">
                    <span class="webprofiler-tab-label">
                        <?php echo $tab['tab']['label'] ?>
                    </span>
                    <span class="webprofiler-tab-value">
                        <?php echo $tab['tab']['value'] ?>
                    </span>
                    <span class="webprofiler-tab-counter">
                        <?php echo $tab['tab']['count'] ?>
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
            </div>
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
        foreach ($panels as $panel) {
        ?>
            <div class="webprofiler-panel">

            </div>
        <?php
        }
        ?>
    </div>
</div>
