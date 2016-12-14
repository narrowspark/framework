<div id="webprofiler-<?php echo $token ?>" class="webprofiler">
    <a class="show-button" title="Show WebProfiler" tabindex="-1" accesskey="D"><?php echo file_get_contents($icons['ic_narrowspark_white_24px.svg']) ?></a>
    <div class="webprofiler-header">
        <?php
            if (count($menus) !== 0) {
                ?>
        <div class="webprofiler-menus">
            <a class="hide-button" title="Close WebProfiler" tabindex="-1" accesskey="D"><?php echo file_get_contents($icons['ic_clear_white_24px.svg']) ?></a>
        <?php
                foreach ($menus as $name => $menu) {
                    $tooltip = false;

                    if (isset($menu['tooltip'])) {
                        $tooltip = true;
                    }

                    $href = isset($panels[$name]) ? 'data-panel-target-id="webprofiler-panel-' . $name . '"' : '';
                    $hasPanels = isset($panels[$name]) ? ' webprofiler-menu-has-panel' : '';
                    $hasTooltip = $tooltip ? ' webprofiler-menu-has-tooltip' : '';
                    $cssClasses = isset($menu['menu']['class']) ? ' ' . $menu['menu']['class'] : ''; ?>
            <a <?php echo $href ?> class="webprofiler-menu webprofiler-menu-<?php echo $name ?> webprofiler-menu-position-<?php echo $menu['position'];
                    echo $hasPanels;
                    echo $hasTooltip;
                    echo $cssClasses ?>">
                <div class="webprofiler-menu-content">
                    <?php if (isset($menu['menu']['icon'])) {
                        ?>
                    <span class="webprofiler-menu-icon">
                        <?php echo isset($icons[$menu['menu']['icon']]) ? file_get_contents($icons[$menu['menu']['icon']]) : $menu['menu']['icon'] ?>
                    </span>
                    <?php

                    } ?>
                    <?php if (isset($menu['menu']['status'])) {
                        ?>
                    <span class="webprofiler-menu-status">
                        <?php echo $menu['menu']['status'] ?>
                    </span>
                    <?php

                    } ?>
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

                    } ?>
            </a>
        <?php

                } ?>
        </div>
        <?php

            }
        ?>
    </div>
    <div class="webprofiler-body">
        <?php
        foreach ($panels as $name => $panel) {
            ?>
            <div class="webprofiler-panel webprofiler-panel-<?php echo $name; echo $panel['class']; ?>">
                <?php echo $panel['content'] ?>
            </div>
        <?php

        }
        ?>
    </div>
</div>
