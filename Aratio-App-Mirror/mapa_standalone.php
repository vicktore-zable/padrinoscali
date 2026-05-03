<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Puestos de Votación - A Ratio</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        body { margin: 0; padding: 0; font-family: sans-serif; }
        #map { height: 100vh; width: 100%; }
        .info-box {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            max-width: 300px;
        }
    </style>
</head>
<body>

<div id="map"></div>
<div class="info-box">
    <h3>Puestos de Votación</h3>
    <p>Mostrando los puestos georreferenciados en el Valle.</p>
    <a href="/" style="display:block; margin-top:10px; text-decoration:none; color:#ae9454;">Volver al Inicio</a>
</div>

<script>
    var map = L.map('map').setView([3.4516, -76.5320], 10); // Cali default

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    fetch('api_puestos_standalone.php')
        .then(response => response.json())
        .then(data => {
            var markers = L.layerGroup();
            
            data.forEach(puesto => {
                if (puesto.latitud && puesto.longitud) {
                    var marker = L.marker([puesto.latitud, puesto.longitud])
                        .bindPopup(`<b>${puesto.puesto}</b><br>${puesto.municipio}, ${puesto.departamento}`);
                    markers.addLayer(marker);
                }
            });
            
            markers.addTo(map);
            if (markers.getLayers().length > 0) {
                 map.fitBounds(new L.featureGroup(markers.getLayers()).getBounds());
            }
        })
        .catch(error => console.error('Error loading map data:', error));
</script>

</body>
</html>
