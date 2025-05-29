<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Chronicle\ChangeLog\Renderer\Generic as GenericRenderer;
use DecodeLabs\Chronicle\ChangeLog\Options;
use DecodeLabs\Effigy;

trait ChangelogRendererTrait
{
    private function getChangelogRenderer(): GenericRenderer
    {
        $manifest = Effigy::$project->getLocalManifest();
        $settings = $manifest->getExtra()->changelog;

        $options = new Options(
            issueAssignees: $settings->issueAssignees->as('?bool') ?? true,
            pullRequestAssignees: $settings->pullRequestAssignees->as('?bool') ?? true
        );

        return new GenericRenderer($options);
    }
}
