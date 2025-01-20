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

namespace ui\Component\Input\Field;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\Input\Field\Textarea;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;


/**
 * Class TextareaRTE
 */
class TextareaRTE extends Input implements Textarea {
    use JavaScriptBindable;
    use Triggerer;

    protected string $label;
    protected ?string $byline;
    protected bool $is_required = false;
    protected bool $is_disabled = false;
    protected ?Constraint $requirement_constraint = null;

    protected ?int $max_limit = null;

    protected ?int $min_limit = null;
    private array $rteSupport = [];

    public function __construct(string $label, ?string $byline = null)
    {
        global $DIC;

        $this->label = $label;
        $this->byline = $byline;

        parent::__construct(new Factory(), $DIC->refinery());
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withLabel(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getByline(): ?string
    {
        return $this->byline;
    }

    /**
     * @inheritdoc
     */
    public function withByline(string $byline): self
    {
        $clone = clone $this;
        $clone->byline = $byline;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * @inheritdoc
     */
    public function withRequired(bool $is_required, ?Constraint $requirement_constraint = null): self
    {
        $clone = clone $this;
        $clone->is_required = $is_required;
        $clone->requirement_constraint = ($is_required) ? $requirement_constraint : null;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled(): bool
    {
        return $this->is_disabled;
    }

    /**
     * @inheritdoc
     */
    public function withDisabled(bool $is_disabled): self
    {
        $clone = clone $this;
        $clone->is_disabled = $is_disabled;
        return $clone;
    }

    public function withOnUpdate(Signal $signal): self
    {
        return $this->withTriggeredSignal($signal, 'update');
    }

    /**
     * @inheritdoc
     */
    public function appendOnUpdate(Signal $signal): self
    {
        return $this->appendTriggeredSignal($signal, 'update');
    }

    public function withMaxLimit(int $max_limit): TextareaRTE
    {
        $clone = $this->withAdditionalTransformation(
            $this->refinery->string()->hasMaxLength($max_limit)
        );

        $clone->max_limit = $max_limit;

        return $clone;
    }

    /**
     * get maximum limit of characters
     * @return int|null
     */
    public function getMaxLimit(): ?int
    {
        return $this->max_limit;
    }

    /**
     * set minimum number of characters
     */
    public function withMinLimit(int $min_limit): TextareaRTE
    {
        $clone = $this->withAdditionalTransformation(
            $this->refinery->string()->hasMinLength($min_limit)
        );

        $clone->min_limit = $min_limit;

        return $clone;
    }

    /**
     * get minimum limit of characters
     * @return int|null
     */
    public function getMinLimit(): ?int
    {
        return $this->min_limit;
    }

    protected function isClientSideValueOk($value): bool
    {
        return is_string($value);
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        if ($this->min_limit) {
            return $this->refinery->string()->hasMinLength($this->min_limit);
        }
        return $this->refinery->string()->hasMinLength(1);
    }

    public function isLimited(): bool
    {
        return $this->min_limit > 0 || $this->max_limit > 0;
    }

    public function getUpdateOnLoadCode(): \Closure
    {
        return fn($id) => "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').val());";
    }

    public function setRTESupport(
        int $obj_id,
        string $obj_type,
        string $module,
        ?string $cfg_template = null,
        bool $hide_switch = false,
        ?string $version = null
    ): void {
        $this->rteSupport = array(
            "obj_id" => $obj_id,
            "obj_type" => $obj_type,
            "module" => $module,
            'cfg_template' => $cfg_template,
            'hide_switch' => $hide_switch,
            'version' => $version
        );
    }

    public function getRTESupport(): array
    {
        return $this->rteSupport;
    }
}