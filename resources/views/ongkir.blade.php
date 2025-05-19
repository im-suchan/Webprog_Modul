<!DOCTYPE html>
<html>

<head>
    <title>Cek Ongkir</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <form id="ongkirForm">
        <select name="province" id="province">
            <option value="">Pilih Provinsi</option>
        </select>

        <select name="city" id="city">
            <option value="">Pilih Kota</option>
        </select>

        <input type="number" name="weight" id="weight" placeholder="Berat (gram)">

        <select name="courier" id="courier">
            <option value="">Pilih Kurir</option>
            <option value="jne">JNE</option>
            <option value="tiki">TIKI</option>
            <option value="pos">POS Indonesia</option>
        </select>

        <button type="submit">Cek Ongkir</button>
    </form>

    <div id="result"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Load Provinces
            fetch('/provinces')
                .then(response => response.json())
                .then(data => {
                    console.log('Provinces data:', data);
                    if (data.rajaongkir.status.code === 200) {
                        const provinces = data.rajaongkir.results;
                        const provinceSelect = document.getElementById('province');
                        provinces.forEach(province => {
                            const option = document.createElement('option');
                            option.value = province.province_id;
                            option.textContent = province.province;
                            provinceSelect.appendChild(option);
                        });
                    } else {
                        console.error('Gagal memuat provinsi:', data.rajaongkir.status.description);
                    }
                })
                .catch(error => {
                    console.error('Error fetching provinces:', error);
                });

            // Load Cities when province changes
            document.getElementById('province').addEventListener('change', function () {
                const provinceId = this.value;
                fetch(`/cities?province_id=${provinceId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Cities data:', data);
                        if (data.rajaongkir.status.code === 200) {
                            const cities = data.rajaongkir.results;
                            const citySelect = document.getElementById('city');
                            citySelect.innerHTML = '<option value="">Pilih Kota</option>';
                            cities.forEach(city => {
                                const option = document.createElement('option');
                                option.value = city.city_id;
                                option.textContent = city.city_name;
                                citySelect.appendChild(option);
                            });
                        } else {
                            console.error('Gagal memuat kota:', data.rajaongkir.status.description);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching cities:', error);
                    });
            });

            // Cek Ongkir Form Submission
            document.getElementById('ongkirForm').addEventListener('submit', function (event) {
                event.preventDefault();

                const origin = 501; // Misal: Kota Yogyakarta
                const destination = document.getElementById('city').value;
                const weight = document.getElementById('weight').value;
                const courier = document.getElementById('courier').value;

                fetch('/cost', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        origin: origin,
                        destination: destination,
                        weight: weight,
                        courier: courier
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Cost data:', data);
                        if (data.rajaongkir.status.code === 200) {
                            const costs = data.rajaongkir.results[0].costs;
                            const resultDiv = document.getElementById('result');
                            resultDiv.innerHTML = '';

                            costs.forEach(cost => {
                                const div = document.createElement('div');
                                div.textContent = `${cost.service} : ${cost.cost[0].value} Rupiah (${cost.cost[0].etd} hari)`;
                                resultDiv.appendChild(div);
                            });
                        } else {
                            console.error('Gagal menghitung ongkir:', data.rajaongkir.status.description);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching cost:', error);
                    });
            });
        });
    </script>
</body>

</html>
