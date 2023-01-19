<?php

namespace BewarHizirvan\LaravelGrid;

use Illuminate\Http\Request;
use BewarHizirvan\LaravelGrid\Elements\Column;
use BewarHizirvan\LaravelGrid\Elements\Filter;

class LaravelGrid
{
    // Build wonderful things
    protected $provider;
    protected $with;
    protected $dir = 'left';
    protected $label = null;
    protected $label_extra = null;
    protected $cfg = [];
    protected $filters = [];
    protected $firstCol = '';
    protected $lastCol = '';
    protected $actionCol = false;
    protected $actionColActive = false;
    protected $showActionCol = false;
    protected $actionColActiveInverse = false;
    protected $actionColString = [];
    protected $input;
    protected $total = null;
    protected $CheckPerm = null;
    protected $paginate = 50;
    protected $headerCounter = true;
    protected $footerCounter = true;
    protected $paginateClass = "pagination";
    protected $labelButton = null;
    protected $Tabs10 = "\t\t\t\t\t\t\t\t\t\t";
    protected $Tabs8 = "\t\t\t\t\t\t\t\t";
    protected $counterString = "Showing records %s — %s of %s";

    const PARENT = '<span style="color:ForestGreen">Parent</span>';
    const SUBMENU = '<span style="color:LightCoral">SubMenu</span>';
    const OK = '<span style="color:ForestGreen;text-align:center"><i class="fas fa-check"></i></span>';
    const NOTOK = '<span style="color:LightCoral;text-align:center"><i class="fas fa-times"></i></span>';
    const ENABLED = '<span style="color:ForestGreen">Enabled</span>';
    const DISABLED = '<span style="color:LightCoral">Disabled</span>';

    public function __construct($parameters = [])
    {
        $this->input = request();
        if(isset($parameters['checkClass'])) $this->CheckPerm = $parameters['checkClass'];
        if(isset($parameters['paginate'])) $this->paginate = $parameters['paginate'];
        if(isset($parameters['provider'])) $this->provider = $parameters['provider'];
        if(isset($parameters['dir'])) $this->dir = $parameters['dir'];
        if(isset($parameters['label'])) $this->label = $parameters['label'];
        if(isset($parameters['label_extra'])) $this->label_extra = $parameters['label_extra'];
        if(isset($parameters['counterString'])) $this->counterString = $parameters['counterString'];
        if(isset($parameters['headerCounter'])) $this->headerCounter = $parameters['headerCounter'];
        if(isset($parameters['footerCounter'])) $this->footerCounter = $parameters['footerCounter'];
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    public function setLabelButton($label = 'New', $route = '')
    {
        $this->labelButton = "<a href=\"$route\" class=\"btn btn-primary\">$label</a>";
    }

    public function orderBy($col,$dir)
    {
        $input = $this->input;
        if($input->input('sort') == null)
        {
            if (is_a($this->provider , "Illuminate\Database\Eloquent\Builder"))
            {
                $this->provider->orderBy($col,$dir);
            }else
            {
                if($dir == 'asc')
                    $this->provider = $this->provider->sortBy($col);
                else
                    $this->provider = $this->provider->sortByDesc($col);
            }
        }


    }

    public function setTotal($count)
    {
        $this->total = $count;
    }

    public function setIPsortable($name)
    {
        $colid = array_filter($this->cfg, function ($item) use ($name)
        {
            return $item->getName() == $name;

        });
        $colid = array_keys($colid);
        $this->cfg[$colid[0]]->setIPsortable();
    }

    public function getColumnByName($name)
    {
        $colid = array_filter($this->cfg, function ($item) use ($name)
        {
            return $item->getName() == $name;

        });
        $colid = array_keys($colid);
        return $this->cfg[$colid[0]];
    }

    public function addColumn($name = 'id', $label = 'id',$filter = false, $sortable = false, $ValueCalculator = null, $ValueFormatter = null)
    {
        $col = new Column($name,$label);
        if($ValueCalculator != null)
            $col->setValueCalculator($ValueCalculator);
        if($ValueFormatter != null)
            $col->setValueFormatter($ValueFormatter);
        $this->cfg[] = $col;
        $this->lastCol = $name;
        if(count($this->cfg)==1)
            $this->firstCol = $name;
        if($filter) $this->addFilter($name, $label);
        if($sortable) $col->setSortable();
    }

    public function addFilter($name = 'id', $label = 'id', $operator = 'like', $options = null, $evaluate = true)
    {
        $filter = new Filter($name, $label, $operator, $options, $evaluate);
        $value = $this->input->input(str_replace('.','_',$name));
        $filter->setValue($value);
        $this->filters[] = $filter;
    }

    public function addFilterSelect($name = 'id', $label = 'id', $options = [], $evaluate = true)
    {
        //$this->filters[] = new Filter($name, $label, '=', $options = [], $evaluate);
        $filter = new Filter($name, $label, '=', $options, $evaluate);
        $value = $this->input->input(str_replace('.','_',$name));
        $filter->setValue($value);
        $this->filters[] = $filter;
    }

    public function addColumnBranch($name = 'branch.name', $filter = true)
    {
        $this->addColumn($name,trans('db.branch'));
        if($filter)
        {
            $branchs = [''=>trans('db.branch').': -'];
            if(!$this->CheckUser->can('list_all_branch') && $this->CheckUser->can('list_all_callcenter',false))
                $branchs += Branch::WhereIn('branchid',[1,2])->orderBy('name')->pluck('name','branchid')->toarray();
            elseif( !( $this->CheckUser->can('list_all_branch') || $this->CheckUser->can('list_all') ) )
                $branchs += Branch::where('branchid','=',\Auth::user()->branchid)->orderBy('name')->pluck('name','branchid')->toarray();
            elseif($this->CheckUser->can('list_all_branch',false))
                $branchs += Branch::where('branchid','!=',\Config::get('paik.superbranch'))->orderBy('name')->pluck('name','branchid')->toarray();
            else
                $branchs += Branch::orderBy('name')->pluck('name','branchid')->toarray();
            $name_array = explode('.',$name);
            if(count($name_array)==2) $this->addFilter('branchid', trans('db.branch'),'=',$branchs);
            elseif(count($name_array)==3) $this->addFilter("$name_array[0].branchid", trans('db.branch'),'=',$branchs);
        }

    }

    public function addActionColumn($col = 'id', $active = false, $inverse = false)
    {
        $this->actionCol = $col;
        $this->actionColActive = $active;
        $this->actionColActiveInverse = $inverse;
    }

    public function addActionButton($type = 'default', $title='', $route = '/', $conditions = [], $colid = null)
    {
        switch($type)
        {
            case 'edit':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-edit\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'attach':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-paperclip\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'attachView':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s', '_blank', 'toolbar=no,scrollbars=yes,resizable=yes,top=500,left=500,width=1000,height=830');\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-eye\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'editbranch':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<span class=\"fa-layers\">".PHP_EOL
                    .$this->Tabs10."			<i class=\"fas fa-edit\"></i>".PHP_EOL
                    .$this->Tabs10."			<span class=\"fa-layers-text fa-inverse\" data-fa-transform=\"shrink-3 up-3 left-12\">b</span>".PHP_EOL
                    .$this->Tabs10."		</span>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'editclient':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<span class=\"fa-layers\">".PHP_EOL
                    .$this->Tabs10."			<i class=\"fas fa-edit\"></i>".PHP_EOL
                    .$this->Tabs10."			<span class=\"fa-layers-text fa-inverse\" data-fa-transform=\"shrink-3 up-3 left-12\">c</span>".PHP_EOL
                    .$this->Tabs10."		</span>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'diagram':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s', '_blank', 'toolbar=no,scrollbars=no,resizable=yes,top=500,left=500,width=1000,height=830');\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-sitemap\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'receipt':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-arrow-alt-to-bottom\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'remove':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"javascript:checkDelete('%s');\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-times\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'complete':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-check\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'print':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s', '_blank', 'toolbar=no,scrollbars=no,resizable=no,top=400,left=500,width=850,height=900');\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-print\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'printA5':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s', '_blank', 'toolbar=no,scrollbars=no,resizable=no,top=400,left=500,width=600,height=850');\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-print\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'pdf':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s', '_blank', 'toolbar=no,scrollbars=no,resizable=no,top=500,left=500,width=1200,height=600');\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-file-pdf\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'email':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s', '_blank', 'toolbar=no,scrollbars=no,resizable=no,top=500,left=500,width=1200,height=600');\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-envelope\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'activity':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-list\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'account':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-search\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'account_blank':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s')\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-search\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'password':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s', '_blank', 'toolbar=no,scrollbars=no,resizable=no,top=500,left=500,width=400,height=270');\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-info\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'traffic':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"window.open('%s')\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-exchange\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'dormant':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"if(confirm('".trans('db.makedormant')."')) location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-recycle\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'undormant':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"if(confirm('".trans('db.makeundormant')."')) location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-undo\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'lock':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-lock-alt\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'disable':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-lock\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'disableConfirm':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"if(confirm('".trans('db.disable')."')) location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-lock\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'unlock':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-reply\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'control':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-check\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'session':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-times-circle\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'disconnect':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-minus-circle\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'smsreply':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-reply\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'add':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-plus\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'requestDisable':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-eject\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'disabled_info':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs disabled-request-button\" title=\"%s\" style=\"background-color: LightCoral;\" >".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-question\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            case 'disabled_request_info':
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs disabled-request-button\" title=\"%s\"  style=\"background-color: #F5B041;\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-question\" style1=\"color: #F5B041;\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;
                break;

            default:
                $icon = $this->Tabs10."	<button type=\"button\" class=\"btn btn-default btn-xs\" title=\"".$title."\" onClick=\"document.location.href='%s'\">".PHP_EOL
                    .$this->Tabs10."		<i class=\"fas fa-edit\"></i>".PHP_EOL
                    .$this->Tabs10."	</button>".PHP_EOL;

        }

        $this->actionColString[] = [
            'type' => $type,
            'title' => $title,
            'route' => $route,
            'icon' => $icon,
            'conditions' => $conditions,
            'colid' => $colid
        ];

    }

    public static function addContextMenu($value = '', $title = ['name'=>'','value'=>''], $rows = [], $right=false)
    {
        $result = PHP_EOL.'										<div class="thumbs" onClick="javascript:showDetails($(this));" onmouseleave="javascript:hideDetails($(this));" ondblclick="javascript:copyTextToClipboard(\''.htmlspecialchars($value).'\');">'.PHP_EOL
            .'											<span>'.$value.'</span>'.PHP_EOL
            .'											<div class="details-pane'.(($right)?' rightside':'').'">'.PHP_EOL
            .'												<table class="table  table-striped table-hover table-bordered">'.PHP_EOL
            .'													<thead class="t1">'.PHP_EOL
            .'														<tr class="t1">'.PHP_EOL
            .'															<th class="t1">'.$title['name'].'</th><th class="t1">'.$title['value'].'</th>'.PHP_EOL
            .'														</tr>'.PHP_EOL
            .'													</thead>'.PHP_EOL
            .'													<tbody class="t1">'.PHP_EOL;

        foreach ($rows as $key => $val)
        {
            $result .= '														<tr class="t1">'.PHP_EOL
                .'															<td class="t1">'.$key.'</td>'.PHP_EOL
                .'															<td class="t1">'.$val.'</td>'.PHP_EOL
                .'														</tr>'.PHP_EOL;

        }
        $result .='													</tbody>'.PHP_EOL
            .'												</table>'.PHP_EOL
            .'											</div><!-- @end .details-pane -->'.PHP_EOL
            .'										</div><!-- @end .thumbs -->';

        return $result;
    }

    public function ActionColumnRender()
    {
        if($this->actionCol)
        {
            $check = $this->CheckPerm;
            $action_func = function ($row) use ($check) {

                $colname = $this->actionCol;
                $active = $this->actionColActive;
                $col = "".PHP_EOL;
                if($this->actionColActive)
                    if(($row->$active == 0 && !$this->actionColActiveInverse) || ($row->$active == 1 && $this->actionColActiveInverse))
                        $col .=$this->Tabs10."<div  style=\"background-color: LightCoral\">";
                foreach($this->actionColString as $btn)
                {

                    $cond = true;
                    foreach($btn['conditions'] as $condition)
                    {

                        if( isset($condition['before']) && $condition['before'] == '&&' )
                        {
                            if($condition['name'] == 'perm') $cond = $cond && ( is_null($check)?true:( $check->can($condition['value'])?true:false ) );
                            elseif($condition['name'] == 'function')
                            {
                                $function = $condition['value'];
                                eval('$t = '."( $function )?true:false;");
                                $cond = $cond && $t;
                            }
                            else
                            {
                                $tcol = $condition['name'];
                                $names_array = explode(".", $tcol);
                                if(count($names_array) == 2)
                                {
                                    $tb1 = $names_array[0];
                                    $tb2 = $names_array[1];
                                    if(!empty($row->$tb1))
                                        $name = $row->$tb1->$tb2;
                                    else
                                        $name = $row->$tb1;
                                }

                                else
                                    $name = $row->$tcol;
                                $operation = $condition["operation"];
                                $value = $condition["value"];
                                eval('$t = '."($name $operation $value )?true:false;");
                                $cond = $cond && $t;


                            }
                        }
                        elseif( isset($condition['before']) && $condition['before'] == '||' )
                        {
                            if($condition['name'] == 'perm') $cond = $cond || ( is_null($check)?true:( $check->can($condition['value'])?true:false ) );
                            elseif($condition['name'] == 'function')
                            {
                                $function = $condition['value'];
                                eval('$t = '."( $function )?true:false;");
                                $cond = $cond || $t;
                            }
                            else
                            {
                                $tcol = $condition['name'];
                                $name = $row->$tcol;
                                $operation = $condition["operation"];
                                $value = $condition["value"];
                                eval('$t = '."($name $operation $value )?true:false;");
                                $cond = $cond || $t;
                            }
                        }
                        else
                        {
                            if($condition['name'] == 'perm') $cond = is_null($check)?true:( $check->can($condition['value'])?true:false );
                            elseif($condition['name'] == 'function')
                            {
                                $function = $condition['value'];
                                eval('$t = '."( $function )?true:false;");
                                $cond = $t;
                            }
                            else
                            {
                                $tcol = $condition['name'];
                                $name = $row->$tcol;
                                $operation = $condition["operation"];
                                $value = $condition["value"];
                                eval('$t = '."($name $operation $value )?true:false;");
                                $cond = $t;
                            }
                        }

                    }
                    if($cond)
                    {
                        $this->showActionCol = true;
                        if(isset($btn['colid']))
                        {
                            $ar = [];
                            foreach($btn['colid'] as $key => $val)
                            {
                                $relate = explode(".", $val);
                                if(count($relate)==1)
                                    $ar[$key] = $row->$val;
                                else
                                    $ar[$key] = $row->$relate[0]->$relate[1];

                            }
                            $col .= sprintf($btn['icon'], route($btn['route'],$ar));
                        }else
                        {
                            if(is_callable($btn['route']))
                            {
                                $title = $btn['route']($row);
                                $col .= sprintf($btn['icon'], $title);
                            }else
                                $col .= sprintf($btn['icon'], route($btn['route'],$row->$colname));
                        }

                    }
                }
                if($this->actionColActive)
                    if(($row->$active == 0 && !$this->actionColActiveInverse) || ($row->$active == 1 && $this->actionColActiveInverse))
                        $col .=$this->Tabs10."</div>";
                $col .=$this->Tabs10;
                return $col;

            };
            $this->addColumn('action',trans('db.action'), null,null,$action_func);

        }

    }

    protected function count()
    {
        if($this->total != null)
            return $this->total;
        else
            return $this->getQuery()->count();
    }

    public function getQuery()
    {
        $input = $this->input;
        $query = clone $this->provider;
        if($this->input->input('sort') != null)
        {
            if (is_a($this->provider , "Illuminate\Database\Eloquent\Builder"))
            {
                $array = explode("-", $this->input->input('sort'));
                $dir = $array[count($array)-1];
                array_pop($array);
                array_pop($array);
                if(count($array) == 1)
                {
                    $col = $array[0];
                    $isIP = $this->getColumnByName($col)->getIPsortable();
                    if($isIP) $query = $query->orderBy(\DB::Raw('INET_ATON('.$col.')'),$dir);
                    else $query = $query->orderBy($col,$dir);
                }
                elseif(count($array) == 2)
                {
                    $tbl = $array[0];
                    $col = $array[1];
                    $isIP = $this->getColumnByName("$tbl.$col")->getIPsortable();
                    if($isIP) $query = $query->with([$tbl => function($q) use ($col,$dir) { return $q->orderBy(\DB::Raw('INET_ATON('.$col.')'),$dir);}]);
                    else  $query = $query->with([$tbl => function($q) use ($col,$dir) { return $q->orderBy($col ,$dir);}]);

                }
                elseif(count($array) == 3)
                {
                    $tbl = $array[0];
                    $col = $array[1];
                    $col1 = $array[2];
                    $isIP = $this->getColumnByName("$tbl.$col.$col1")->getIPsortable();
                    if($dir == 'asc')
                    {
                        if($isIP)
                            $query = $query->sortBy(function($q) use ($col1,$col,$tbl) { return ip2long($q->$tbl->$col->$col1);});
                        else
                            $query = $query->sortBy(function($q) use ($col1,$col,$tbl) { return isset($q->$tbl->$col)?$q->$tbl->$col->$col1:'';});
                    }else
                    {
                        if($isIP)
                            $query = $query->sortByDesc(function($q) use ($col1,$col,$tbl) { return ip2long($q->$tbl->$col);});
                        else
                            $query = $query->sortByDesc(function($q) use ($col1,$col,$tbl) { return isset($q->$tbl->$col)?$q->$tbl->$col->$col1:'';});
                    }
                }
                $col = str_replace('-dir-'.$dir,'',$this->input->input('sort'));
                $col = str_replace('-','.',$col);

            }
            else
            {


                $array = explode("-", $this->input->input('sort'));
                $dir = $array[count($array)-1];
                array_pop($array);
                array_pop($array);
                if(count($array) == 1)
                {
                    $col = $array[0];
                    $isIP = $this->getColumnByName($col)->getIPsortable();
                    if($dir == 'asc')
                    {
                        if($isIP)
                            $query = $query->sortBy(function($q) use ($col) { return ip2long($q->$col);});
                        else
                            $query = $query->sortBy($col);
                    }else
                    {
                        if($isIP)
                            $query = $query->sortByDesc(function($q) use ($col) { return ip2long($q->$col);});
                        else
                            $query = $query->sortByDesc($col);
                    }
                }
                elseif(count($array) == 2)
                {
                    $tbl = $array[0];
                    $col = $array[1];
                    $isIP = $this->getColumnByName("$tbl.$col")->getIPsortable();
                    if($dir == 'asc')
                    {
                        if($isIP)
                            $query = $query->sortBy(function($q) use ($col,$tbl) { return ip2long($q->$tbl->$col);});
                        else
                            $query = $query->sortBy(function($q) use ($col,$tbl) { return isset($q->$tbl)?$q->$tbl->$col:'';});
                    }else
                    {
                        if($isIP)
                            $query = $query->sortByDesc(function($q) use ($col,$tbl) { return ip2long($q->$tbl->$col);});
                        else
                            $query = $query->sortByDesc(function($q) use ($col,$tbl) { return isset($q->$tbl)?$q->$tbl->$col:'';});
                    }
                }
                elseif(count($array) == 3)
                {
                    $tbl = $array[0];
                    $col = $array[1];
                    $col1 = $array[2];
                    $isIP = $this->getColumnByName("$tbl.$col.$col1")->getIPsortable();
                    if($dir == 'asc')
                    {
                        if($isIP)
                            $query = $query->sortBy(function($q) use ($col1,$col,$tbl) { return ip2long($q->$tbl->$col->$col1);});
                        else
                            $query = $query->sortBy(function($q) use ($col1,$col,$tbl) { return isset($q->$tbl->$col)?$q->$tbl->$col->$col1:'';});
                    }else
                    {
                        if($isIP)
                            $query = $query->sortByDesc(function($q) use ($col1,$col,$tbl) { return ip2long($q->$tbl->$col);});
                        else
                            $query = $query->sortByDesc(function($q) use ($col1,$col,$tbl) { return isset($q->$tbl->$col)?$q->$tbl->$col->$col1:'';});
                    }
                }
            }

        }

        foreach($this->filters as $filter)
        {
            //echo $filter->getName();
            $getId = str_replace('.','_',$filter->getName());
            //echo $getId; echo $input->input($getId);
            $names_array = explode(".", $filter->getName());
            if($input->input($getId) !== null && $input->input($getId) != "" && $filter->getEvaluate())
            {
                if(count($names_array) == 3)
                {

                    $table = $names_array[0];
                    $column = $names_array[1];
                    $column1 = $names_array[2];
                    $value = $input->input($getId);
                    if($filter->getOperator() == "like")
                    {
                        if (is_a($this->provider , "Illuminate\Database\Eloquent\Builder"))
                        {
                            $query->whereHas($table, function ($query) use ($column1,$column,$value) { $query->whereHas($column, function ($query) use ($column1,$value) { $query->where($column1, 'like', '%'.$value.'%'); }); });
                        }else
                        {
                            $query = $query->filter(function ($item) use ($table, $column, $value)
                            {
                                if(isset($item->$table))
                                {
                                    if(isset($item->$table->$column))
                                    {
                                        return false !== stristr($item->$table->$column, $value);
                                    }else
                                        return false;
                                }else
                                    return false;
                            });
                        }

                    }else
                        $query = $query->whereHas($table, function ($query) use ($column,$value) { $query->where($column, '=', $value); });
                }
                elseif(count($names_array) == 2)
                {

                    $table = $names_array[0];
                    $column = $names_array[1];
                    $value = $input->input($getId);
                    if($filter->getOperator() == "like")
                    {
                        if (is_a($this->provider , "Illuminate\Database\Eloquent\Builder"))
                        {
                            $query->whereHas($table, function ($query) use ($column,$value) { $query->where($column, 'like', '%'.$value.'%'); });
                        }else
                        {
                            $query = $query->filter(function ($item) use ($table, $column, $value)
                            {
                                if(isset($item->$table))
                                {
                                    if(isset($item->$table->$column))
                                    {
                                        return false !== stristr($item->$table->$column, $value);
                                    }else
                                        return false;
                                }else
                                    return false;
                            });
                        }

                    }else
                        $query = $query->whereHas($table, function ($query) use ($column,$value) { $query->where($column, '=', $value); });
                }
                elseif(count($names_array) == 1)
                {


                    if($filter->getOperator() == "like")
                    {
                        if (is_a($this->provider , "Illuminate\Database\Eloquent\Builder"))
                        {
                            $fname = $filter->getName();
                            $fvalue = $input->input($getId);
                            $query->where($fname,'like','%'.$fvalue.'%');
                        }else
                        {
                            $fname = $filter->getName();
                            $fvalue = $input->input($getId);
                            $query = $query->filter(function ($item) use ($fname, $fvalue)
                            {
                                if(is_array($item)) $item = (object)$item;
                                if(isset($item->$fname))
                                    return false !== stristr($item->$fname, $fvalue);
                                else
                                    return false;
                            });
                        }
                        //$query = $query->where($filter->getName(),'like','%'.$input->input($getId).'%');

                    }
                    else
                        $query = $query->where($filter->getName(),'=',$input->input($getId));
                }

            }

        }
        return $query;
    }

    protected function fullUrlwithoutSort()
    {
        $query_string = "?";
        $url = $this->input->url();
        $query = $this->input->except('sort');
        foreach($query as $key => $value)
        {
            $query_string .= "$key=$value&";
        }
        $result = $url.$query_string;
        return $result;
    }

    public function counterHTML()
    {
        $count = $this->count();
        $page_number = ($this->input->input('page') !== null)?$this->input->input('page'):1;
        $showing_min = (((($page_number>0)?$page_number:1)-1)*$this->paginate)+1;
        $showing_mid = (($this->paginate < $count)?$this->paginate:$count);
        $showing_max = (((($page_number>0)?$page_number:1)-1)*$this->paginate)+$showing_mid;
        $showing_max = ($showing_max < $count )?$showing_max:$count;
        return sprintf($this->counterString,$showing_min, $showing_max, $count);

    }

    public function titlesHTML()
    {
        $header_titles = [];
        $uri_parameters = $this->fullUrlwithoutSort();

        foreach($this->cfg as $col)
        {
            $colName = str_replace('.','-',$col->getName());
            $header_titles[] = [
                'class' => "column-$colName",
                'value' => $col->getLabel(),
                'sortable' => $col->getSortable(),
                'uri' => $uri_parameters."sort=$colName-dir-"];

        }
        return $header_titles;

    }

    public function filtersHTML()
    {
        $header_filters = [];
        foreach($this->filters as $filter)
        {
            $filterName = str_replace('.','_',$filter->getName());
            $header_filters[] = [
                'name' => $filterName,
                'placeholder' => $filter->getLabel(),
                'value' => $filter->getValue(),
                'options' => $filter->getOptions()

            ];
        }
        return $header_filters;
    }

    public function tbodyHTML()
    {
        $tbody = [];
        $page_number = ($this->input->input('page') !== null)?$this->input->input('page'):1;
        if (is_a($this->provider , "Illuminate\Database\Eloquent\Builder"))
        {
            if($page_number != null & $this->total == null)
                $query = $this->getQuery()->skip(($page_number-1)*$this->paginate)->take($this->paginate)->get();
            else
                $query = $this->getQuery()->take($this->paginate)->get();
        }else
        {
            if($page_number != null & $this->total == null)
                $query = $this->getQuery()->slice(($page_number-1)*$this->paginate)->take($this->paginate)->all();
            else
                $query = $this->getQuery()->take($this->paginate)->all();
        }
        foreach($query as $row)
        {
            $row = (object) $row;
            $tbody_row = [];
            foreach($this->cfg as $col)
            {
                $value = '';
                $colName = $col->getName();
                $names_array = explode(".", $colName);
                switch(count($names_array))
                {
                    case 1:
                        $key = $names_array[0];
                        $value = isset($row->$key)?$row->$key:'';
                        break;
                    case 2:
                        $key0 = $names_array[0];
                        $key1 = $names_array[1];
                        if(isset($row->$key0))
                            $sub_row = collect($row->$key0);
                        if(isset($sub_row))
                            $value = $sub_row->get($key1);
                        else
                            $value = '';
                        //$value = $key0.$key1;
                        $sub_row = null;
                        break;
                    case 3:
                        $key0 = $names_array[0];
                        $key1 = $names_array[1];
                        $key2 = $names_array[2];
                        if(isset($row->$key0->$key1))
                            $sub_row = collect($row->$key0->$key1);
                        if(isset($sub_row))
                            $value = $sub_row->get($key2);
                        else
                            $value = '';
                        //$value = $key0.$key1;
                        $sub_row = null;
                        break;
                }

                if(!is_null($col->getValueCalculator()))
                    $value = $col->getValueCalculator()($row,$value);
                if(!is_null($col->getValueFormatter()))
                    $value = $col->getValueFormatter()($value);
                $tbody_row[] = [
                    'label' => $col->getLabel(),
                    'class' => 'column-'.str_replace('.','-',$colName),
                    'value' => $value
                ];
            }
            $tbody[] = $tbody_row;

        }
        return $tbody;
    }

    public function paginationHTML()
    {
        $count = $this->count();

        if($count > $this->paginate)
        {
            $links = [];
            $firstPageUrl = null;
            $lastPageUrl = null;
            $url = $this->input->url();
            $currentPage = ($this->input->input('page') !== null)?$this->input->input('page'):1;
            $total_pages = floor($count / $this->paginate) + ((($count % $this->paginate) != 0)?1:0);
            $input_query = "";
            foreach($this->input->except(['page']) as $key => $value)
            {
                $input_query .= "$key=$value&";
            }
            $input_query = str_replace('%', '',$input_query);
            if($currentPage > 2) $firstPageUrl = $url.'?page='.(1)."&$input_query";
            $previousPageUrl = $url.'?page='.($currentPage-1)."&$input_query";
            $hasMorePages = ($currentPage != $total_pages)?true:false;
            $nextPageUrl = $url.'?page='.($currentPage+1)."&$input_query";
            if($total_pages - $currentPage > 1) $lastPageUrl = $url.'?page='.($total_pages)."&$input_query";
            if($total_pages > 6)
            {
                if($currentPage - 2 >= 2) $links[] = '...';
                if($currentPage - 2 >= 2) $links[] = [$currentPage - 2 => $url.'?page='.($currentPage-2)."&$input_query"];
                $links[] = [$currentPage => $url.'?page='.($currentPage)."&$input_query"];
                if($currentPage + 2 <= $total_pages - 1) $links[] = [$currentPage + 2 => $url.'?page='.($currentPage+2)."&$input_query"];
                if($total_pages - $currentPage > 2) $links[] = '...';
            }
            else{
                $links[] = '...';
                $links[] = [$currentPage => $url.'?page='.($currentPage)."&$input_query"];
                $links[] = '...';
            }
            return [
                'onFirstPage' => ( ($currentPage == 1)? true:false ),
                'currentPage' => $currentPage,
                'firstPageUrl' => $firstPageUrl,
                'lastPageUrl' => $lastPageUrl,
                'previousPageUrl' => $previousPageUrl,
                'hasMorePages' => $hasMorePages,
                'nextPageUrl' => $nextPageUrl,
                'links' => $links,
            ];
        }
        else return null;
    }

    public function render()
    {
        $grid = [];
        $this->ActionColumnRender();
        $header_colspan = count($this->cfg);
        $grid['dir'] = $this->dir;
        $grid['label'] = $this->label;
        $grid['label_extra'] = $this->label_extra;
        $grid['header_colspan'] = $header_colspan;
        $grid['header_counter'] = ($this->headerCounter)?$this->counterHTML():null;
        $grid['header_titles'] = $this->titlesHTML();
        $grid['header_filters'] = $this->filtersHTML();
        $grid['footer_counter'] = ($this->footerCounter)?$this->counterHTML():null;
        $grid['tbody'] = $this->tbodyHTML();
        $grid['pagination'] = $this->paginationHTML();
        if(!is_null($this->labelButton)) $grid['label_extra'] = $this->labelButton;

        return view('laravelgrid::grid', compact('grid'));

    }
}
