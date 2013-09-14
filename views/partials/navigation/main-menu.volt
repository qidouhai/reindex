<ul class="list pills large">
  {% for item in mainMenu %}
  <li{{ (item['name'] == controllerName) ? ' class="active"' : '' }}><a href="http://{{ item['path']~serverName }}"><i class="icon-{{ item['icon'] }}" ></i>&nbsp;{{ item['label'] }}</a></li>
  {% endfor %}
  <li class="space"></li>
  {% if controllerName == 'index' %}
  <li class="icon"><a class="icon" href="http://twitter.com/prg_it"><i class="icon-twitter icon-large"></i></a></li>
  <li class="icon"><a class="icon" href="http://facebook.com/programmazione.it"><i class="icon-facebook icon-large"></i></a></li>
  <li class="icon"><a class="icon" href="#"><i class="icon-google-plus icon-large"></i></a></li>
  {% elseif controllerName == 'forum' %}
  <li><a class="button" href="#"><i class="icon-question"></i>&nbsp;FAI UNA DOMANDA</a></li>
  {% elseif controllerName == 'links' %}
  <li><a class="button" href="#"><i class="icon-link"></i> AGGIUNGI UN LINK</a></li>
  {% endif %}
</ul>