import 'package:flutter/material.dart';
import '../utils/constants.dart';

class HelpScreen extends StatelessWidget {
  const HelpScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bantuan'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Pertanyaan yang Sering Diajukan (FAQ)',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 24),
            _buildFaqItem(
              question: 'Bagaimana cara meminjam ruangan?',
              answer: '1. Login ke aplikasi\n'
                  '2. Pilih ruangan yang ingin dipinjam\n'
                  '3. Klik tombol "Pinjam Ruangan"\n'
                  '4. Isi formulir peminjaman\n'
                  '5. Klik "Ajukan Peminjaman"\n'
                  '6. Tunggu persetujuan dari petugas',
            ),
            _buildFaqItem(
              question: 'Berapa lama proses persetujuan peminjaman?',
              answer: 'Proses persetujuan peminjaman biasanya membutuhkan waktu 1-2 hari kerja tergantung ketersediaan petugas.',
            ),
            _buildFaqItem(
              question: 'Bagaimana cara membatalkan peminjaman?',
              answer: '1. Buka menu "Peminjaman Saya"\n'
                  '2. Pilih peminjaman yang ingin dibatalkan\n'
                  '3. Klik tombol "Batalkan Peminjaman"\n'
                  '4. Konfirmasi pembatalan',
            ),
            _buildFaqItem(
              question: 'Bagaimana jika saya lupa kata sandi?',
              answer: 'Saat ini, fitur reset kata sandi belum tersedia. Silakan hubungi administrator untuk mendapatkan bantuan.',
            ),
            const SizedBox(height: 32),
            const Text(
              'Kontak Dukungan',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            _buildContactItem(
              icon: Icons.email,
              title: 'Email',
              content: 'support@peminjaman-ruang.com',
            ),
            _buildContactItem(
              icon: Icons.phone,
              title: 'Telepon',
              content: '+62 812 3456 7890',
            ),
            _buildContactItem(
              icon: Icons.location_on,
              title: 'Alamat',
              content: 'Jl. Contoh No. 123, Jakarta Pusat',
            ),
            const SizedBox(height: 32),
            const Text(
              'Tentang Aplikasi',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Aplikasi Peminjaman Ruangan v1.0.0\n\n'
              'Dikembangkan oleh Tim Developer UKK 2023\n\n'
              '© 2023-2025 Seluruh hak cipta dilindungi',
              style: TextStyle(fontSize: 14),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildFaqItem({required String question, required String answer}) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: ExpansionTile(
        title: Text(
          question,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            color: AppColors.primary,
          ),
        ),
        children: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Align(
              alignment: Alignment.topLeft,
              child: Text(answer),
            ),
          ),
          const SizedBox(height: 8),
        ],
      ),
    );
  }

  Widget _buildContactItem({
    required IconData icon,
    required String title,
    required String content,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            color: AppColors.primary,
            size: 24,
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 4),
                Text(content),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
