<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Cetak</title>
</head>

<body>
	<div style="width: 500px; margin: auto;">
		<br>
		<center>
			<?php echo $toko['nama'] ?? 'Nama Toko Tidak Tersedia'; ?><br> 
			<?php echo $toko['alamat'] ?? 'Alamat tidak tersedia'; ?><br><br>
			<table width="100%">
				<tr>
					<td><?php echo $nota ?></td>
					<td align="right"><?php echo $tanggal ?></td>
				</tr>
			</table>
			<hr>
			<table width="100%">
				<tr>
					<td width="50%"></td>
					<td width="3%"></td>
					<td width="10%" align="right"></td>
					<td align="right" width="17%"><?php echo $kasir ?></td>
				</tr>
				<?php foreach ($produk as $item): ?>
					<tr>
						<td><?php echo $item['nama_produk'] ?></td>
						<td></td>
						<td align="right"><?php echo $item['total_qty'] ?></td>
						<td align="right"><?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
						<td align="right"><?php echo number_format($item['total_harga'], 0, ',', '.'); ?></td>
					</tr>
				<?php endforeach ?>
			</table>
			<hr>
			<table width="100%">
				<tr>
					<td width="76%" align="right">
						Harga Jual
					</td>
					<td width="23%" align="right">
						<?php echo $total ?>
					</td>
				</tr>
			</table>
			<hr>
			<table width="100%">
				<tr>
					<td width="76%" align="right">
						Total
					</td>
					<td width="23%" align="right">
						<?php echo $total ?>
					</td>
				</tr>
				<tr>
					<td width="76%" align="right">
						Bayar
					</td>
					<td width="23%" align="right">
						<?php echo $bayar ?>
					</td>
				</tr>
				<tr>
					<td width="76%" align="right">
						Kembalian
					</td>
					<td width="23%" align="right">
						<?php echo $kembalian ?>
					</td>
				</tr>
			</table>
			<br>
			Terima Kasih <br>
		</center>
	</div>
	<script>
		window.print()
	</script>
</body>

</html>