<?php declare(strict_types=1);
use Viserio\Component\Profiler\TemplateManager;

if (! isset($token, $menus, $icons, $panels)) {
    return;
}
?>
<div id="profiler" data-token="profiler-<?php echo TemplateManager::escape($token); ?>" class="profiler">
    <a class="profiler-show-button" title="Show Profiler" tabindex="-1">
        <?php echo \file_get_contents($icons['ic_narrowspark_white_24px.svg']); ?>
    </a>
    <div class="profiler-header">
        <?php if (\count($menus) !== 0): ?>
        <div class="profiler-menus">
            <a class="profiler-hide-button" title="Close Profiler" tabindex="-1">
                <?php echo \file_get_contents($icons['ic_clear_white_24px.svg']); ?>
            </a>
            <?php foreach ($menus as $name => $menu):
                $tooltip = false;

                if (isset($menu['tooltip'])) {
                    $tooltip = true;
                }

                $data       = isset($panels[$name]) ? 'data-panel-target-id="profiler-panel-' . TemplateManager::escape($name) . '"' : '';
                $hasPanels  = isset($panels[$name]) ? ' profiler-menu-has-panel' : '';
                $hasTooltip = $tooltip ? ' profiler-menu-has-tooltip' : '';
                $cssClasses = isset($menu['menu']['class']) ? ' ' . $menu['menu']['class'] : '';
            ?>
            <div <?php echo $data; ?> class="profiler-menu profiler-menu-<?php echo TemplateManager::escape($name); ?> profiler-menu-position-<?php echo $menu['position'] . $hasPanels . $hasTooltip . $cssClasses; ?>">
                <div class="profiler-menu-content">
                    <?php if (isset($menu['menu']['icon'])): ?>
                    <span class="profiler-menu-icon">
                        <?php echo isset($icons[$menu['menu']['icon']]) ? \file_get_contents($icons[$menu['menu']['icon']]) : $menu['menu']['icon']; ?>
                    </span>
                    <?php endif; ?>
                    <span class="profiler-menu-label">
                        <?php echo TemplateManager::escape((string) $menu['menu']['label']); ?>
                    </span>
                    <span class="profiler-menu-value">
                        <?php echo TemplateManager::escape((string) $menu['menu']['value']); ?>
                    </span>
                </div>
                <?php if ($tooltip): ?>
                    <div class="profiler-menu-tooltip">
                        <?php echo $menu['tooltip']; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="profiler-body">
        <div class="profiler-body-menu">
            <a class="profiler-body-close-panel"  title="Close panel" tabindex="-1">
                <?php echo \file_get_contents($icons['ic_clear_white_24px.svg']); ?>
            </a>
            <a class="profiler-body-resize-panel"  title="Resize body" tabindex="-1">
                <?php echo \file_get_contents($icons['ic_keyboard_arrow_up_white_24px.svg']); ?>
            </a>
        </div>
        <?php foreach ($panels as $name => $panel): ?>
        <div class="profiler-panel profiler-panel-<?php echo TemplateManager::escape($name) . TemplateManager::escape($panel['class']); ?>">
            <?php echo $panel['content']; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
