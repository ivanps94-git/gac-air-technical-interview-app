{% extends 'index.html.twig' %}

{% block title %}New Users{% endblock %}

{% block body %}
<h1>Create new Users</h1>

{{ include('users/_form.html.twig') }}

<a href="{{ path('users_index') }}">back to list</a>
{% endblock %}
