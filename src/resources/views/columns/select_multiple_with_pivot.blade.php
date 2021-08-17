{{-- single relationships (1-1, 1-n) --}}
<td>
		@if ($entry->{$column['entity']})
			@foreach ($column['attribute'] as $attribute)
                @if(!strpos($attribute, "."))
                    {{ str_replace(" 00:00:00","",$entry->{$column['entity']}->{$attribute}) }} 
                @else
                    {{ $entry->{$column['entity']}->{ substr($attribute,0,strpos($attribute,".")) }->{ substr($attribute,strpos($attribute, ".")+1,strlen($attribute)) } }} 
                @endif                         
            @endforeach
	    @endif
</td>