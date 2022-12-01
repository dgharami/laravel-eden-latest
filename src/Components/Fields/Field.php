<?php

namespace Dgharami\Eden\Components\Fields;

use Dgharami\Eden\Components\Form;
use Dgharami\Eden\Traits\CanManageVisibility;
use Dgharami\Eden\Traits\DependentField;
use Dgharami\Eden\Traits\Makeable;
use Dgharami\Eden\Traits\AsDataTableColumn;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpParser\Node\Expr\ClosureUse;

/**
 * @method static static make(mixed $name, string $key = null)
 */
abstract class Field
{
    use Makeable;
    use CanManageVisibility;
    use AsDataTableColumn;
    use DependentField;

    public ?string $title = '';

    protected $key = '';

    protected $uid = '';

    protected $show = true;

    protected $createRules = '';

    protected $updateRules = '';

    protected $messages = [];

    protected $helpText = '';

    protected $value = '';

    protected $resolveCallback = null;

    protected $required = false;

    protected $prefix = '';

    protected $suffix = '';

    protected $options = [];

    protected $meta = [
        'type' => 'text',
        'class' => 'border-0-force focus:ring-0 grow'
    ];

    protected $validator = null;

    protected $transformCallback = null;

    protected function __construct($title, $key = null)
    {
        $this->title = $title;
        $this->key = is_null($key) ? Str::snake(Str::lower($title)) : $key;
        $this->uid = Str::lower('__' . Str::random());

        if (method_exists($this, 'onMount')) {
            $this->onMount();
        }
    }

    /**
     * Prepare anything for this field before rendering and procession on "Form" component
     *
     * @param Form $form
     * @return void
     */
    public function prepare(Form $form)
    {
        // Nothing to do
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string|\Closure $key
     */
    public function key($key)
    {
        $this->key = appCall($key);
        return $this;
    }

    /**
     * @return string|array
     */
    public function getRules($isUpdate = false)
    {
        if (!$isUpdate) {
            return $this->createRules;
        }
        return $this->updateRules;
    }

    /**
     * @param string|array $rules
     */
    public function rules($rules)
    {
        $this->createRules = is_string($rules) ? explode('|', $rules) : $rules;
        $this->updateRules = is_string($rules) ? explode('|', $rules) : $rules;
        $this->required = in_array('required', $this->createRules) || in_array('required', $this->updateRules);
        return $this;
    }

    /**
     * @param string|array $rules
     */
    public function createRules($rules)
    {
        $this->createRules = is_string($rules) ? explode('|', $rules) : $rules;
        $this->required = in_array('required', $this->createRules);
        return $this;
    }

    /**
     * @param string|array $rules
     */
    public function updateRules($rules)
    {
        $this->updateRules = is_string($rules) ? explode('|', $rules) : $rules;
        $this->required = in_array('required', $this->updateRules);
        return $this;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Add Frontend Mark as Required
     *
     * @return $this
     */
    public function required()
    {
        $this->required = true;
        return $this;
    }

    /**
     * @return string
     */
    public function getHelpText(): string
    {
        return $this->helpText;
    }

    /**
     * @param string $helpText
     */
    public function helpText(string $helpText)
    {
        $this->helpText = $helpText;
        return $this;
    }

    /**
     * Prefix of the Field
     *
     * @param $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->prefix = appCall($prefix);
        return $this;
    }

    /**
     * Suffix of the Field
     *
     * @param $prefix
     * @return $this
     */
    public function suffix($suffix)
    {
        $this->suffix = appCall($suffix);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->exportToForm();
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function default($value)
    {
        $this->setValue($value);
        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = appCall($value);
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function options(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param mixed $this
     */
    public function resolve($callback = null)
    {
        $this->resolveCallback = $callback;
        return $this;
    }

    /**
     * @param mixed $this
     */
    public function resolveUsing($value, $fields = [], $form = null)
    {
        if (!is_null($this->resolveCallback)) {
            $targets = collect($fields)
                ->filter(function ($item, $key) {
                    return in_array($key, $this->targets);
                });
            return appCall($this->resolveCallback, [
                'field' => $this,
                'value' => $value,
                'fields' => collect($fields),
                'targets' => $targets,
                'target' => $targets->first(),
                'form' => $form,
            ]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    public function withMeta(array $meta)
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    function getMetaAttributes()
    {
        if(!$this->meta) return '';

        // Remove Unwanted Keys
        if (isset($this->meta['id']))
            unset($this->meta['id']);

        if (isset($this->meta['wire:model']))
            unset($this->meta['wire:model']);

        if (isset($this->meta['wire:model.defer']))
            unset($this->meta['wire:model.defer']);

        $compiled = join('="%s" ', array_keys($this->meta)).'="%s"';

        return vsprintf($compiled, array_map('htmlspecialchars', array_values($this->meta)));
    }

    /**
     * Get transform callback
     *
     * @return null
     */
    public function getTransformCallback()
    {
        return $this->transformCallback;
    }

    /**
     * Transform field value to another
     *
     * @param mixed $transform
     */
    public function transform($transform)
    {
        $this->transformCallback = $transform;
        return $this;
    }

    /**
     * Is this read only
     *
     * @return $this
     */
    public function readOnly()
    {
        $this->meta = array_merge($this->meta, [
            'readonly' => 'readonly'
        ]);
        return $this;
    }

    /**
     * Make field disabled
     *
     * @return $this
     */
    public function disabled()
    {
        $this->meta = array_merge($this->meta, [
            'disabled' => 'disabled'
        ]);
        return $this;
    }

    /**
     * Should hide from front end
     *
     * @return $this
     */
    public function hide($should = true)
    {
        $this->show = !appCall($should);
        return $this;
    }

    /**
     * Should show in front end
     *
     * @return $this
     */
    public function show($should = true)
    {
        $this->show = appCall($should);
        return $this;
    }

    /**
     * @return boolean
     */
    public function shouldShow()
    {
        return $this->show;
    }

    public function exportToForm()
    {
        return $this->value();
    }

    public function importFromFrom($value, $fields = [])
    {
        $this->setValue($value);
        return $this;
    }

    public function process()
    {
        return $this->value();
    }

    public function validate($isUpdate = false)
    {
        return Validator::make(
            [
                $this->key => $this->value
            ],
            [
                $this->key => $this->getRules($isUpdate)
            ]
        );
    }

    public function isValid($isUpdate = false)
    {
        $this->validator = $this->validate($isUpdate);
        if ($this->validator instanceof \Illuminate\Contracts\Validation\Validator) {
            return !$this->validator->fails();
        }
        return $this->validator;
    }

    private function defaultViewParams()
    {
        return [
            'title' => $this->title,
            'key' => $this->key,
            'uid' => $this->uid,
            'helpText' => $this->helpText,
            'value' => $this->value,
            'options' => $this->options,
            'meta' => $this->meta,
            'required' => $this->required,
            'attributes' => $this->getMetaAttributes(),
            'validator' => $this->validator,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'isDependent' => $this->hasDependency,
            'wireModelType' => $this->hasDependency ? 'lazy' : 'defer',
            'alpineModelType' => $this->hasDependency ? '' : '.defer',
        ];
    }

    public function viewForRead()
    {
        return view('eden::fields.view.text');
    }

    public function view()
    {
        return view('eden::fields.input.text');
    }

    public function render($type = 'form')
    {
        $viewToRender = '';

        switch (strtolower($type)):
            case 'table-header':
                $viewToRender = $this->viewForIndexHeader();
                break;
            case 'table-row':
                $viewToRender = $this->viewForIndex();
                break;
            case 'read':
                $viewToRender = $this->viewForRead();
                break;
            default:
                $viewToRender = $this->view();
        endswitch;

        if ($viewToRender instanceof View) {
            $viewToRender = $viewToRender->with($this->defaultViewParams());

            return $viewToRender->render();
        }

        return $viewToRender;
    }

    public function dump()
    {
        return [
            'title' => $this->title,
            'key' => $this->key,
            'value' => $this->value,
            'targets' => $this->targets,
            'isDependent' => $this->hasDependency
        ];
    }

}
