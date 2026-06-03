<?php
declare(strict_types=1);

namespace Himatsudo\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\Template;

final class QiqRenderer implements RenderInterface
{
    private Template $template;

    public function __construct()
    {
        $appDir  = dirname(__DIR__, 2);
        $cacheDir = $appDir . '/var/tmp';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $this->template = Template::new(
            paths: [$appDir . '/var/qiq/templates'],
            extension: '.html.php',
            cacheDir: $cacheDir,
        );
    }

    public function render(ResourceObject $ro): string
    {
        $templateName = $this->resolveTemplateName($ro);
        $body         = (array) ($ro->body ?? []);

        $this->template->setData($body);
        $this->template->setView($templateName);
        return ($this->template)();
    }

    private function resolveTemplateName(ResourceObject $ro): string
    {
        // Allow resource to override template name
        if (isset($ro->body['_template'])) {
            return (string) $ro->body['_template'];
        }

        $class = get_class($ro);
        // Strip namespace down to Resource\Page\... portion
        $relative = (string) preg_replace('/^.*\\\\Resource\\\\Page\\\\/', '', $class);
        $parts    = explode('\\', $relative);

        return match (true) {
            $parts === ['Index']    => 'top/index',
            $parts === ['Articles'] => 'articles/index',
            $parts === ['Article']  => 'articles/detail',
            default                 => strtolower(implode('/', $parts)),
        };
    }
}
