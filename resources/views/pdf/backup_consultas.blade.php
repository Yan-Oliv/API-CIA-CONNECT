<h2>Relatório de Consultas - {{ $data }}</h2>
<p>Total de consultas: {{ $total }}</p>

<h4>Por Empresa:</h4>
<pre>{{ print_r($porEmpresa, true) }}</pre>

<h4>Por Buony:</h4>
<pre>{{ print_r($porBuony, true) }}</pre>

<h4>Por Status:</h4>
<pre>{{ print_r($porStatus, true) }}</pre>

<h4>Por Filial:</h4>
<pre>{{ print_r($porFilial, true) }}</pre>

<h4>Lista Detalhada:</h4>
<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>Empresa</th>
            <th>Motorista</th>
            <th>Buony</th>
            <th>Status</th>
            <th>Consulta</th>
            <th>Destino</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dados as $item)
            <tr>
                <td>{{ $item['empresa'] }}</td>
                <td>{{ $item['motorista'] }}</td>
                <td>{{ $item['buony'] }}</td>
                <td>{{ $item['status'] }}</td>
                <td>{{ $item['consulta'] }}</td>
                <td>{{ $item['destino'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
