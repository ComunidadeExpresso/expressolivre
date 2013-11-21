// Use essa função para validar datas no formado dd/mm/yyyy
function check_date(input,caption)
{
    var re   = /[0-3][0-9]\/[0-1][0-9]\/[1-2][0-9][0-9][0-9]/;
    var data = input.value;

    if (!data.match(re))
    {
        alert('Data Inválida ('+caption+'). Utilize o formato dd/mm/yyyy');
        return false;
    } else {
        return true;
    }
}

// Use essa função para validar numeros
function check_num(input,caption)
{
    var re   = /[0-9]+/;
    var num = input.value;

    if (!num.match(re))
    {
        alert('Número Inválido ('+caption+').');
        return false;
    } else {
        return true;
    }
}
