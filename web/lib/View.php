<?php
declare(strict_types=1);

/**
 * Minimal view renderer: renders a template from web/views into the shared
 * layout. Templates receive $vars as local variables and the current user as
 * $currentUser.
 */
function fxweb_view(string $template, array $vars = [], ?string $title = null): void
{
    $vars['__title'] = $title ?? 'فروشگاه';
    if (!array_key_exists('currentUser', $vars)) {
        $vars['currentUser'] = fxweb_current_user($GLOBALS['fxpdo']);
    }

    $file = __DIR__ . '/../views/' . preg_replace('/[^a-z0-9_]/i', '', $template) . '.php';
    if (!is_file($file)) {
        http_response_code(500);
        exit('template not found');
    }

    extract($vars, EXTR_SKIP);
    ob_start();
    include $file;
    $__content = ob_get_clean();

    include __DIR__ . '/../views/layout.php';
}
