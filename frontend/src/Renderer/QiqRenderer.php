<?php
declare(strict_types=1);

namespace Himatsudo\Frontend\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;
use Qiq\Template;

final class QiqRenderer implements RenderInterface
{
    private Template $template;

    public function __construct()
    {
        $appDir = dirname(__DIR__, 2);
        $this->template = Template::new(
            paths: [$appDir . '/var/qiq/templates'],
            extension: '.html.php',
        );
    }

    public function render(ResourceObject $ro): string
    {
        $templateName = $this->resolveTemplateName($ro);
        $body         = (array) ($ro->body ?? []);

        foreach ($body as $key => $value) {
            $this->template->$key = $value;
        }

        return $this->template->render($templateName);
    }

    private function resolveTemplateName(ResourceObject $ro): string
    {
        // Allow resource to override template name
        if (isset($ro->body['_template'])) {
            return (string) $ro->body['_template'];
        }

        $class = get_class($ro);
        // Remove namespace prefix: Himatsudo\Frontend\Resource\App\
        $relative = (string) preg_replace('/^.*\\\\Resource\\\\App\\\\/', '', $class);
        // Split on backslash
        $parts = explode('\\', $relative);

        return match (true) {
            $parts === ['Index']                   => 'top/index',
            $parts === ['Articles']                => 'articles/index',
            $parts === ['Article']                 => 'articles/detail',
            default                                => strtolower(implode('/', $parts)),
        };
    }
}
