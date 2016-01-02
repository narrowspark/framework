<?php
namespace Viserio\View;

class VirtuosoView extends View
{
    /**
     * Get the string contents of the view.
     *
     * @param callable|null $callback
     *
     * @return string
     */
    public function render(callable $callback = null)
    {
        $contents = $this->renderContents();

        $response = isset($callback) ? call_user_func($callback, $this, $contents) : null;

        // Once we have the contents of the view, we will flush the sections if we are
        // done rendering all views so that there is nothing left hanging over when
        // another view gets rendered in the future by the application developer.
        $this->factory->getVirtuoso()->flushSectionsIfDoneRendering();

        return $response !== null ? $response : $contents;
    }

    /**
     * Get the sections of the rendered view.
     *
     * @return array
     */
    public function renderSections()
    {
        return $this->render(function () {
            return $this->factory->getVirtuoso()->getSections();
        });
    }

    /**
     * Get the contents of the view instance.
     *
     * @return string
     */
    protected function renderContents()
    {
        // We will keep track of the amount of views being rendered so we can flush
        // the section after the complete rendering operation is done. This will
        // clear out the sections for any separate views that may be rendered.
        $this->factory->getVirtuoso()->incrementRender();

        $this->factory->getVirtuoso()->callComposer($this);

        $contents = $this->getContents();

        // Once we've finished rendering the view, we'll decrement the render count
        // so that each sections get flushed out next time a view is created and
        // no old sections are staying around in the memory of an environment.
        $this->factory->getVirtuoso()->decrementRender();

        return $contents;
    }
}
