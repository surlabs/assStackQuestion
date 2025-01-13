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
use ILIAS\Data\Result;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Button\Button;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ilLanguage;

/**
 * Class ButtonSection
 */
class ButtonSection extends Input implements FormInput
{
    use JavaScriptBindable;
    use Triggerer;

    protected array $buttons;
    protected string $label;
    protected ?string $byline;
    protected bool $is_required = false;
    protected bool $is_disabled = false;
    protected ?Constraint $requirement_constraint = null;

    private ilLanguage $lng;

    public function __construct(array $buttons, string $label, ?string $by_line = null)
    {
        global $DIC;

        $this->lng = $DIC->language();

        $this->setButtons($buttons);
        $this->label = $label;
        $this->byline = $by_line;

        parent::__construct(new Factory(), $DIC->refinery());
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function setButtons(array $buttons): void
    {
        $this->buttons = $buttons;
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

    public function withOnUpdate(Signal $signal): self
    {
        return $this->withTriggeredSignal($signal, 'update');
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
        if (count($this->getButtons()) !== count($value)) {
            return false;
        }
        foreach ($this->getButtons() as $key => $button) {
            if (!array_key_exists($key, $value)) {
                return false;
            }
            if ($button instanceof Button) {
                return false;
            }
        }
        return true;
    }

    public function withInput(InputData $input): self
    {
        return $this;
    }

    public function getContent(): Result
    {
        return $this->data_factory->ok($this->getButtons());
    }
}