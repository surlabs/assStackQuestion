<?php

/**
 * This file is part of the STACK Question plugin for ILIAS, an advanced STEM assessment tool.
 *  This plugin is developed and maintained by SURLABS and is a port of STACK Question for Moodle,
 *  originally created by Chris Sangwin.
 *
 *  The STACK Question plugin for ILIAS is open-source and licensed under GPL-3.0.
 *  For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 *  To report bugs or participate in discussions, visit the Mantis system and filter by
 *  the category "STACK Question" at https://mantis.ilias.de.
 *
 *  More information and source code are available at:
 *  https://github.com/surlabs/STACK
 *
 *  If you need support, please contact the maintainer of this software at:
 *  stack@surlabs.es
 *
 */

declare(strict_types=1);

namespace Customizing\global\plugins\Modules\TestQuestionPool\Questions\assStackQuestion\classes\ui\Component\Input\Field;

use Closure;
use ILIAS\Data\Factory;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Group as GroupInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\Group as GroupInternals;
use ILIAS\UI\Implementation\Component\Input\GroupInternal;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ilLanguage;

/**
 * Class ExpandableSection
 */
class ExpandableSection extends Input implements FormInput, GroupInterface, GroupInternal
{
    use GroupInternals;
    use JavaScriptBindable;
    use Triggerer;

    protected string $label;
    protected ?string $byline;
    private bool $expanded_by_default = false;
    protected bool $is_required = false;
    protected bool $is_disabled = false;
    protected ?Constraint $requirement_constraint = null;

    private ilLanguage $lng;

    public function __construct(array $inputs, string $label, ?string $by_line = null)
    {
        global $DIC;

        $this->lng = $DIC->language();

        $this->setInputs($inputs);
        $this->label = $label;
        $this->byline = $by_line;

        parent::__construct(new Factory(), $DIC->refinery());
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function withLabel(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    public function getByline(): ?string
    {
        return $this->byline;
    }

    public function withByline(string $byline): self
    {
        $clone = clone $this;
        $clone->byline = $byline;
        return $clone;
    }

    public function isExpandedByDefault(): bool
    {
        return $this->expanded_by_default;
    }

    public function withExpandedByDefault(bool $expanded_by_default): self
    {
        $clone = clone $this;
        $clone->expanded_by_default = $expanded_by_default;
        return $clone;
    }

    public function isRequired(): bool
    {
        return $this->is_required;
    }

    public function withRequired(bool $is_required, ?Constraint $requirement_constraint = null): self
    {
        $clone = clone $this;
        $clone->is_required = $is_required;
        $clone->requirement_constraint = ($is_required) ? $requirement_constraint : null;
        return $clone;
    }

    public function isDisabled(): bool
    {
        return $this->is_disabled;
    }

    public function withDisabled(bool $is_disabled): self
    {
        $clone = clone $this;
        $clone->is_disabled = $is_disabled;
        return $clone;
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return function () {
            /*
             * Currently, there is no use case for Group here. The single Inputs
             * within the Group are responsible for handling getUpdateOnLoadCode().
             */
        };
    }

    public function withNameFrom(NameSource $source, ?string $parent_name = null): self
    {
        $clone = parent::withNameFrom($source, $parent_name);
        $clone->setInputs($this->nameInputs($source, $clone->getName()));
        return $clone;
    }

    public function withOnUpdate(Signal $signal): ExpandableSection
    {
        $clone = $this->withTriggeredSignal($signal, 'update');
        $clone->setInputs(array_map(fn($i) => $i->withOnUpdate($signal), $this->getInputs()));
        return $clone;
    }

    public function appendOnUpdate(Signal $signal): self
    {
        return $this->appendTriggeredSignal($signal, 'update');
    }

    protected function isClientSideValueOk($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (count($this->getInputs()) !== count($value)) {
            return false;
        }
        foreach ($this->getInputs() as $key => $input) {
            if (!array_key_exists($key, $value)) {
                return false;
            }
            if (!$input->isClientSideValueOk($value[$key])) {
                return false;
            }
        }
        return true;
    }

    protected function getLanguage(): ilLanguage
    {
        return $this->lng;
    }

    protected function getDataFactory(): Factory
    {
        return $this->data_factory;
    }
}