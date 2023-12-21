<div class="card">
    <!-- Card header -->
    <div class="card-header border-0" style="text-align:center">
        <div class="row">
            <div class="{{ !is_null($grid['label_extra'])? "col-7" : "col-12"  }}">
                @if(!is_null($grid['label']))
                    <h3 class="mb-0">{!! $grid['label'] !!}</h3>
                @endif
            </div>
            @if( !is_null($grid['label_extra']) )
                <div class="col-3">
                    {!! $grid['label_extra'] !!}
                </div>
            @endif
        </div>
    </div>
    <!-- Light table -->
    <div class="{{ $grid['div_class'] ?? 'table-responsive-md' }}" dir="{{ ($grid['dir'] == "left")?"ltr":"rtl" }}" id="{{ $grid['div_id'] ?? 'table-responsive-md' }}">
        <form class="form-inline">
            <table class="table align-items-center table-flush table-striped" style="text-align:{{ $grid['dir'] }}">
                <thead class="thead-light">
                @if(isset($grid['header_filters']) && !empty($grid['header_filters']) )
                    <!-- Filters -->
                    <tr class="t1" style="text-align:{{ $grid['dir'] }}">
                        <td class="t1" colspan="{{ $grid['header_colspan'] }}">
                        <span style="margin:4px;line-height:3">
                            @foreach($grid['header_filters'] as $filter)
                                <span data-role="control-container" data-control="filter">
                                <label class="sr-only"></label>&nbsp;
                                    @if(!isset($filter['options']))
                                        <input name="{{ $filter['name'] }}" placeholder="{{ $filter['placeholder'] }}"
                                               type="text" value="{{ $filter['value'] }}"
                                               class="form-control form-custom-control {{ $filter['name'] }}"
                                               onkeyup="if (event.keyCode == 13) submit()"/>
                                    @else
                                        <select name="{{ $filter['name'] }}" type="text"
                                                class="form-control form-custom-control {{ $filter['name'] }}"
                                                onchange="this.form.submit()">
                                            @foreach($filter['options'] as $key => $value)
                                                @if((string) $filter['value'] == (string) $key)
                                                    <option value="{{$key}}" selected="selected">{!! $value !!}</option>
                                                @else
                                                    <option value="{{$key}}">{!! $value !!}</option>
                                                @endif
                                            @endforeach
                                </select>
                                    @endif
                            </span>
                            @endforeach
                            <button type="reset"
                                    onclick="var form = jQuery(this).parents().filter(&quot;form&quot;);form.find(&quot;input:not([type=&#039;submit&#039;]), select&quot;).val(&quot;&quot;);return false;"
                                    class=" btn btn-sm btn-warning">
                                <i class="fas fa-eraser"></i>&nbsp;{{ trans('db.filter_reset') }}
                            </button>
                            <button type="submit" data-role="managed_list_submit_button"
                                    class=" btn btn-sm btn-success">
                                <i class="fas fa-search"></i>&nbsp;{{ trans('db.filter_submit') }}
                            </button>
                        </span>
                        </td>
                    </tr>
                    <!-- Filters end -->
                @endif
                @if(isset($grid['header_counter'])  && !empty($grid['header_counter']) )
                    <!-- Counter -->
                    <tr class="header-counter {{ ($grid['dir'] == "left")?"text-right":"text-left" }}" style="text-align:{{ ($grid['dir'] == "left")?"right":"left" }}">
                        <th colspan="{{ $grid['header_colspan'] }}">{{ $grid['header_counter'] }}</th>
                    </tr>
                    <!-- Counter end -->
                @endif
                @if(isset($grid['header_titles'])  && !empty($grid['header_titles']) )
                    <!-- Title -->
                    <tr>
                        @foreach($grid['header_titles'] as $title)
                            <th class=" {{ $title['class'] }}">
                                {{ $title['value'] }}
                                @if(isset($title['sortable']) && $title['sortable'] )
                                    <small style="white-space: nowrap">
                                        <a title="{{ trans('db.sort_ascending') }}"
                                           style="text-decoration: none; color:dodgerblue;"
                                           href="{{ $title['uri'] }}asc"> &#x25B2; </a>
                                        <a title="{{ trans('db.sort_descending') }}"
                                           style="text-decoration: none; color:dodgerblue;"
                                           href="{{ $title['uri'] }}desc"> &#x25BC; </a>
                                    </small>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                    <!-- Title end -->
                @endif
                </thead>
                <!-- TBody -->
                <tbody>
                @if(isset($grid['tbody'])  && !empty($grid['tbody']) )
                    @foreach($grid['tbody'] as $row)
                        <tr>
                            @foreach($row as $col)
                                <td data-title="{{ $col['label'] }}" class="{{ $col['class'] }}">
                                    {!! $col['value'] !!}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endif
                </tbody>
                <!-- TBody end -->
                @if(isset($grid['footer_counter'])  && !empty($grid['footer_counter']) )
                    <tfoot>
                    <!-- Counter -->
                    <tr class="footer-counter {{ ($grid['dir'] == "left")?"text-right":"text-left" }}" style="text-align:{{ ($grid['dir'] == "left")?"right":"left" }}">
                        <th colspan="{{ $grid['header_colspan'] }}">{{ $grid['footer_counter'] }}</th>
                    </tr>
                    <!-- Counter end -->
                    </tfoot>
                @endif
            </table>
        </form>
        @if(isset($grid['pagination'])  && !empty($grid['pagination']) )
            <nav>
                <ul class="pagination">
                    {{-- First Page Link --}}
                    @if ($grid['pagination']['firstPageUrl'] != null)
                        <li class="page-item">
                            <a class="page-link" href="{{ $grid['pagination']['firstPageUrl'] }}" rel="first"
                               aria-label="@lang('pagination.first')">&lsaquo;&lsaquo;</a>
                        </li>
                    @endif
                    {{-- Previous Page Link --}}
                    @if ($grid['pagination']['onFirstPage'])
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $grid['pagination']['previousPageUrl'] }}" rel="prev"
                               aria-label="@lang('pagination.previous')">&lsaquo;</a>
                        </li>
                    @endif
                    {{-- Pagination Elements --}}
                    @foreach ($grid['pagination']['links'] as $element)
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span
                                    class="page-link">{{ $element }}</span></li>
                        @endif
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $grid['pagination']['currentPage'])
                                    <li class="page-item active" aria-current="page"><span
                                            class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    {{-- Next Page Link --}}
                    @if ($grid['pagination']['hasMorePages'])
                        <li class="page-item">
                            <a class="page-link" href="{{ $grid['pagination']['nextPageUrl'] }}" rel="next"
                               aria-label="@lang('pagination.next')">&rsaquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        </li>
                    @endif
                    {{-- Last Page Link --}}
                    @if ($grid['pagination']['lastPageUrl'] != null)
                        <li class="page-item">
                            <a class="page-link" href="{{ $grid['pagination']['lastPageUrl'] }}" rel="last"
                               aria-label="@lang('pagination.last')">&rsaquo;&rsaquo;</a>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>
</div>
