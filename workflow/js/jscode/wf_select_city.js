var cities = function(data)
{
	if (typeof(data) == "string")
	{
		write_errors(data);
		return false;
	}
	else
	{
		var container = document.getElementById(data["target"]);
		container.innerHTML = "";
		container.disabled = true;
		fill_combo(data["target"], data["cities"]);
		container.disabled = false;
		return true;
	}
};

function draw_cities(target, state_id, callback, handleExpiredSessions)
{
	cExecute("$this.bo_utils.get_cities",
			function (data)
			{
				if (data['error'])
				{
					alert(data['error'].replace(/<br \/>/gi, "\n"));
					if (data['url'])
						if (handleExpiredSessions)
							window.location = data['url'].replace(/\.\./gi, ".");

					return;
				}
				if (cities(data))
				{
					if (callback)
					{
						callback();
					}
				}
			}
			, "state_id=" + state_id + "&target=" + target);
}

function fill_combo(target, cities)
{
	var container = document.getElementById(target);

	for (var i = 0; i < cities.length; i++)
	{
		var option = document.createElement("option");
		option.innerHTML = cities[i].name;
		option.value = cities[i].id;
		container.appendChild(option);
	}
}
