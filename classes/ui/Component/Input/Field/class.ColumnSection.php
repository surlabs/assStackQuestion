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
use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ilLanguage;

/**
 * Class ColumnSection
 */
class ColumnSection extends Input implements FormInput
{
    use JavaScriptBindable;
    use Triggerer;

    protected array $columns;
    protected array $columns_style;
    protected string $label;
    protected ?string $byline;
    protected bool $is_required = false;
    protected bool $is_disabled = false;
    protected ?Constraint $requirement_constraint = null;
    protected ?string $error = null;

    private ilLanguage $lng;

    public function __construct(array $columns, string $label, ?string $by_line = null)
    {
        global $DIC;

        $this->lng = $DIC->language();

        $this->columns = $columns;
        $this->label = $label;
        $this->byline = $by_line;

        parent::__construct(new Factory(), $DIC->refinery());
    }

    public function renderColumnStyle(string $column_name): string
    {
        if (empty($this->columns_style[$column_name])) {
            return "";
        }

        $style = "style='";

        foreach ($this->columns_style[$column_name] as $key => $value) {
            $style .= $key . ":" . $value . ";";
        }

        $style .= "'";

        return $style;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    protected function nameColumns(NameSource $source, string $parent_name): array
    {
        $named_columns = [];

        foreach ($this->getColumns() as $key => $column) {
            $named_columns[$key] = array();

            foreach ($column as $key_input => $input) {
                $named_columns[$key][$key_input] = $input->withNameFrom($source, $parent_name);
            }
        }

        return $named_columns;
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

    public function withColumnStyles(array $styles): self
    {
        $clone = clone $this;
        $clone->columns_style = $styles;
        return $clone;
    }

    /**
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function withInput(InputData $input_data): self
    {
        if (empty($this->getColumns())) {
            return $this;
        }

        $clone = clone $this;

        $columns = [];
        $contents = [];
        $error = false;

        foreach ($this->getColumns() as $key => $in) {
            $inputs = [];
            $inputs_contents = [];

            foreach ($in as $key_input => $input) {
                $inputs[$key_input] = $input->withInput($input_data);
                $content = $inputs[$key_input]->getContent();
                if ($content->isError()) {
                    $error = true;
                } else {
                    $inputs_contents[$key_input] = $content->value();
                }
            }

            $columns[$key] = $inputs;
            $contents[$key] = $inputs_contents;
        }

        $clone->columns = $columns;

        if ($error) {
            $clone->content = $clone->getDataFactory()->error($this->getLanguage()->txt("ui_error_in_group"));
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone->setError("" . $clone->content->error());
        }

        return $clone;
    }

    public function getValue(): array
    {
        $values = [];

        foreach ($this->columns as $column) {
            $values[] = array_map(fn($i) => $i->getValue(), $column);
        }

        return $values;
    }

    public function withValue($value): self
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;
        foreach ($this->getColumns() as $k => $i) {
            $clone->columns[$k] = array_map(fn($j) => $j->withValue($value[$k]), $i);
        }
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
        $clone->setColumns($this->nameColumns($source, $clone->getName()));
        return $clone;
    }

    public function withOnUpdate(Signal $signal): self
    {
        $clone = $this->withTriggeredSignal($signal, 'update');
        $clone->setColumns(array_map(fn($i) => $i->withOnUpdate($signal), $this->getColumns()));
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
        if (count($this->getColumns()) !== count($value)) {
            return false;
        }
        foreach ($this->getColumns() as $key => $column) {
            if (!array_key_exists($key, $value)) {
                return false;
            }

            if (!is_array($value[$key])) {
                return false;
            }

            if (count($column) !== count($value[$key])) {
                return false;
            }

            foreach ($column as $key_input => $input) {
                if (!array_key_exists($key_input, $value[$key])) {
                    return false;
                }
                if (!$input->isClientSideValueOk($value[$key][$key_input])) {
                    return false;
                }
            }
        }
        return true;
    }
    public function getContent(): Result
    {
        if (empty($this->getColumns())) {
            return new Ok([]);
        }
        return parent::getContent();
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(string $error): void
    {
        $this->error = $error;
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