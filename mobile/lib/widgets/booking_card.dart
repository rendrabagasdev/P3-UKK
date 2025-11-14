import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/booking.dart';
import '../utils/constants.dart';
class BookingCard extends StatelessWidget {
  final Booking booking;
  final VoidCallback? onTap;
  final VoidCallback? onCancelTap;

  const BookingCard({
    super.key,
    required this.booking,
    this.onTap,
    this.onCancelTap,
  });

  Color _getStatusColor() {
    switch (booking.status.toLowerCase()) {
      case 'diterima':
        return AppColors.success;
      case 'proses':
        return AppColors.warning;
      case 'ditolak':
        return AppColors.error;
      case 'selesai':
        return AppColors.info;
      default:
        return AppColors.grey;
    }
  }

  String _getFormattedDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('dd MMM yyyy').format(date);
    } catch (e) {
      return dateStr;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      margin: const EdgeInsets.symmetric(vertical: 8),
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      booking.room?.namaRoom ?? 'Ruangan',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: _getStatusColor().withOpacity(0.2),
                      borderRadius: BorderRadius.circular(4),
                      border: Border.all(color: _getStatusColor()),
                    ),
                    child: Text(
                      booking.status,
                      style: TextStyle(
                        color: _getStatusColor(),
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
              const Divider(),
              Row(
                children: [
                  const Icon(Icons.calendar_today, size: 16, color: AppColors.grey),
                  const SizedBox(width: 8),
                  Text(
                    '${_getFormattedDate(booking.tanggalMulai)} - ${_getFormattedDate(booking.tanggalSelesai)}',
                    style: const TextStyle(color: AppColors.grey),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              if (booking.room != null)
                Row(
                  children: [
                    const Icon(Icons.location_on, size: 16, color: AppColors.grey),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        booking.room!.lokasi,
                        style: const TextStyle(color: AppColors.grey),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              const SizedBox(height: 8),
              Text(
                booking.keterangan,
                style: const TextStyle(fontSize: 14),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              if (booking.status.toLowerCase() == 'proses' && onCancelTap != null)
                Align(
                  alignment: Alignment.centerRight,
                  child: TextButton.icon(
                    onPressed: onCancelTap,
                    icon: const Icon(Icons.cancel, color: AppColors.error),
                    label: const Text('Batalkan', style: TextStyle(color: AppColors.error)),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
