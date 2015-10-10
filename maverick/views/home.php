{{template:snippets/header:["page_type"="homepage","main_colour"="green"]}}

<h2>{{page.heading}}</h2>

<br/>
language_culture: {{maverick.language_culture}}
<br/>
remote_addr: {{server.REMOTE_ADDR}}
<br/>

start_form
{{form:2:en_GB}}
end_form

<ul>{{each:page.fruits:snippets/list}}</ul>

<br/>

page_name: {{page.page_name}}
