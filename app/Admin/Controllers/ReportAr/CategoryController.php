<?php

namespace App\Admin\Controllers\ReportAr;

use App\Models\ArReport\Category;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title('Danh muc')
            ->description(trans('admin.list'))
            ->row(function (Row $row) {
                $row->column(7, Category::tree());

                $row->column(5, function (Column $column) {
                    $form = new \Encore\Admin\Widgets\Form();
                    $menuModel = Category::class;
                    $form->select('parent_id', trans('admin.parent_id'))->options($menuModel::selectOptions());
                    $form->text('title', trans('admin.title'))->rules('required');
                    $form->hidden('_token')->default(csrf_token());

                    $column->append((new Box(trans('admin.new'), $form))->style('success'));
                });
            });
    }

    public function treeViewIndex() {
        return Admin::content(function (Content $content) {
            $content->header('Categories');
            $content->body(Category::tree());
        });
    }

    /**
     * Redirect to edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect()->route('admin.auth.menu.edit', ['menu' => $id]);
    }

    /**
     * @return \Encore\Admin\Tree
     */
    protected function treeView()
    {
        $menuModel = Category::class;

        $tree = new Tree(new Category());

        $tree->disableCreate();

        $tree->branch(function ($branch) {
            $payload = "<i class='fa {$branch['icon']}'></i>&nbsp;<strong>{$branch['title']}</strong>";

            if (!isset($branch['children'])) {
                if (url()->isValidUrl($branch['uri'])) {
                    $uri = $branch['uri'];
                } else {
                    $uri = admin_url($branch['uri']);
                }

                $payload .= "&nbsp;&nbsp;&nbsp;<a href=\"$uri\" class=\"dd-nodrag\">$uri</a>";
            }

            return $payload;
        });

        return $tree;
    }

    /**
     * Edit interface.
     *
     * @param string  $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title(trans('admin.menu'))
            ->description(trans('admin.edit'))
            ->row($this->form()->edit($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $menuModel = Category::class;

        $form = new Form(new $menuModel());

        $form->display('id', 'ID');

        $form->select('parent_id', trans('admin.parent_id'))->options($menuModel::selectOptions());
        $form->text('title', trans('admin.title'))->rules('required');

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }

    /**
     * Help message for icon field.
     *
     * @return string
     */
    protected function iconHelp()
    {
        return 'For more icons please see <a href="http://fontawesome.io/icons/" target="_blank">http://fontawesome.io/icons/</a>';
    }
}