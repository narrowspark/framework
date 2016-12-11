<div id="webprofiler-<?php echo $token ?>" class="webprofiler">
    <div class="webprofiler-header">
        <?php
            if (count($menus) !== 0) {
        ?>
        <div class="webprofiler-menus">
            <a class="hide-button" title="Close WebProfiler" tabindex="-1" accesskey="D"><?php echo file_get_contents(__DIR__ . '/../icons/ic_clear_white_24px.svg') ?></a>
        <?php
                foreach ($menus as $name => $menu) {
                    $tooltip = false;

                    if (isset($menu['tooltip'])) {
                        $tooltip = true;
                    }
        ?>
            <a <?php echo isset($panels[$name]) ? 'href="#webprofiler-panel-' . $name . '"' : ''; ?> class="webprofiler-menu webprofiler-menu-<?php echo $name ?> webprofiler-menu-position-<?php echo $menu['position']?><?php echo isset($panels[$name]) ? ' webprofiler-menu-has-panel' : ''; ?><?php if ($tooltip) { ?> webprofiler-menu-has-tooltip <?php } ?><?php if (isset($menu['menu']['class'])) { echo $menu['menu']['class']; } ?>">
                <div class="webprofiler-menu-content">
                    <?php if (isset($menu['menu']['icon'])) { ?>
                    <span class="webprofiler-menu-icon">
                        <?php echo $menu['menu']['icon'] ?>
                    </span>
                    <?php } ?>
                    <?php if (isset($menu['menu']['status'])) { ?>
                    <span class="webprofiler-menu-status">
                        <?php echo $menu['menu']['status'] ?>
                    </span>
                    <?php } ?>
                    <span class="webprofiler-menu-label">
                        <?php echo $menu['menu']['label'] ?>
                    </span>
                    <span class="webprofiler-menu-value">
                        <?php echo $menu['menu']['value'] ?>
                    </span>
                </div>
                <?php
                    if ($tooltip) {
                ?>
                    <div class="webprofiler-menu-tooltip">
                        <?php echo $menu['tooltip'] ?>
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
