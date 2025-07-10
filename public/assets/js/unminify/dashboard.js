function getDays() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const daysInMonth = new Date(year, month, 0).getDate();
    
    // Buat array dari 1 sampai jumlah hari dalam bulan
    return Array.from({length: daysInMonth}, (_, i) => i + 1);
}
function getTodayDate() {
    let today = new Date();
    let year = today.getFullYear();
    let month = (today.getMonth() + 1).toString().padStart(2, '0');
    let day = today.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Variable untuk menyimpan chart instance
let produkTerlarisChart = null;

// Function untuk load produk terlaris dengan periode
function loadProdukTerlaris(periode = '1month') {
    $.ajax({
        url: produk_terlarisUrl,
        type: "get",
        dataType: "json",
        data: {
            periode: periode,
            limit: 6 // Tambah 1 lebih banyak untuk chart yang lebih informatif
        },
        success: function(res) {
            // Destroy chart yang sudah ada
            if (produkTerlarisChart) {
                produkTerlarisChart.destroy();
            }
            
            var el = $("#produkTerlaris").get(0).getContext("2d");
            produkTerlarisChart = new Chart(el, {
                type: "pie",
                data: {
                    labels: res.label,
                    datasets: [{
                        backgroundColor: ["#f56954", "#00a65a", "#f39c12", "#00c0ef", "#3c8dbc", "#d2d6de", "#9c27b0"],
                        data: res.data
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Update info periode
            updatePeriodeInfo(periode, res.total_produk);
        },
        error: function(xhr, status, error) {
            console.error('Error loading produk terlaris:', error);
            $("#produkTerlaris").parent().append('<p class="text-danger">Gagal memuat data produk terlaris</p>');
        }
    });
}

// Function untuk update info periode
function updatePeriodeInfo(periode, totalProduk) {
    const periodeNames = {
        'today': 'Hari Ini',
        '1month': '1 Bulan Terakhir',
        '1year': '1 Tahun Terakhir',
        'all': 'Semua Waktu'
    };
    
    const periodeText = periodeNames[periode] || '1 Bulan Terakhir';
    
    // Update title card
    $('.card-primary .card-title').html(`Produk Terlaris - ${periodeText}`);
    
    // Tambahkan info jumlah produk jika belum ada
    if (!$('.produk-info').length) {
        $('.card-primary .card-body').prepend(`<p class="produk-info text-muted mb-2"></p>`);
    }
    $('.produk-info').text(`Menampilkan ${totalProduk} produk terlaris`);
}

// Event handlers untuk tombol periode (akan ditambahkan di HTML)
$(document).on('click', '.periode-btn', function() {
    const periode = $(this).data('periode');
    
    // Update active state
    $('.periode-btn').removeClass('btn-primary').addClass('btn-outline-primary');
    $(this).removeClass('btn-outline-primary').addClass('btn-primary');
    
    // Load data baru
    loadProdukTerlaris(periode);
});

// Load data dashboard
$.ajax({
    url: transaksi_hariUrl,
    type: "get",
    dataType: "json",
    data: {
        tanggal: getTodayDate()
    },
    success: (res) => {
        $("#transaksi_hari").html(res.total)
    }
});

$.ajax({
    url: transaksi_terakhirUrl,
    type: "get",
    dataType: "json",
    data: {
        tanggal: getTodayDate()
    },
    success: res => {
        $("#transaksi_terakhir").html(res.total || 0);
    },
});

$.ajax({
    url: stok_hariUrl,
    type: "get",
    dataType: "json",
    data: {
        tanggal: getTodayDate()
    },
    success: res => {
        $("#stok_hari").html(res || 0);
    }
});

// Load produk terlaris dengan periode default
loadProdukTerlaris('1month');

$.ajax({
    url: data_stokUrl,
    type: "get",
    dataType: "json",
    success: res => {
        $.each(res, (key, index) => {
            let html = `<li class="list-group-item">
                ${index.nama_produk}
                <span class="float-right">${index.stok}</span>
            </li>`;
            $("#stok_produk").append(html)
        })
    }
});

// Modify the penjualan bulan chart section
$.ajax({
    url: penjualan_bulanUrl,
    type: "post",
    data: { day: getDays() },
    dataType: "json",
    success: res => {
        const days = getDays();
        const salesData = res.slice(0, days.length); // Pastikan data sesuai dengan jumlah hari
        
        // Hitung nilai maksimum untuk sumbu Y
        const maxSales = Math.max(...salesData);
        const yMax = maxSales === 0 ? 10 : Math.ceil(maxSales * 1.1); // Beri padding 10%
        
        // Buat chart
        const ctx = $("#bulanIni").get(0).getContext("2d");
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: days,
                datasets: [{
                    label: "Kuantitas Produk Terjual",
                    backgroundColor: "rgba(40, 167, 69, 0.8)", // Warna hijau lebih segar
                    borderColor: "rgba(40, 167, 69, 1)",
                    borderWidth: 1,
                    data: salesData
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.parsed.y} produk`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Hari',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Jumlah Produk Terjual',
                            font: {
                                weight: 'bold',
                                size: 12
                            }
                        },
                        beginAtZero: true,
                        max: yMax,
                        ticks: {
                            precision: 0, // Pastikan angka bulat
                            stepSize: Math.max(1, Math.floor(yMax / 10)) // Langkah yang wajar
                        },
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)"
                        }
                    }
                }
            }
        });

        // Hitung total penjualan bulan ini
        const totalSales = salesData.reduce((sum, current) => sum + current, 0);
        $("#total-penjualan-bulan").text(`${totalSales} produk terjual`);
    },
    error: err => {
        console.error("Error loading monthly sales data:", err);
        $("#bulanIni").parent().append('<div class="alert alert-danger">Gagal memuat data penjualan</div>');
    }
});