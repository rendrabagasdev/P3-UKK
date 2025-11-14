import 'room.dart';

class Booking {
  final int idBooking;
  final int idUser;
  final int idPetugas;
  final int idRoom;
  final String tipeBooking; // 'hourly' or 'daily'
  final double harga;
  final int durasi; // dalam jam untuk hourly, hari untuk daily
  final String tanggalMulai;
  final String tanggalSelesai;
  final String status;
  final String keterangan;
  final String? createdAt;
  final String? updatedAt;
  final Room? room;

  Booking({
    required this.idBooking,
    required this.idUser,
    required this.idPetugas,
    required this.idRoom,
    this.tipeBooking = 'hourly',
    this.harga = 0,
    this.durasi = 1,
    required this.tanggalMulai,
    required this.tanggalSelesai,
    required this.status,
    required this.keterangan,
    this.createdAt,
    this.updatedAt,
    this.room,
  });

  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      idBooking: json['id_booking'] ?? 0,
      idUser: json['id_user'] ?? 0,
      idPetugas: json['id_petugas'] ?? 0,
      idRoom: json['id_room'] ?? 0,
      tipeBooking: json['tipe_booking'] ?? 'hourly',
      harga: _parseDouble(json['harga'], 0),
      durasi: json['durasi'] ?? 1,
      tanggalMulai: json['tanggal_mulai'] ?? '',
      tanggalSelesai: json['tanggal_selesai'] ?? '',
      status: json['status'] ?? '',
      keterangan: json['keterangan'] ?? '',
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
      room: json['room'] != null ? Room.fromJson(json['room']) : null,
    );
  }

  static double _parseDouble(dynamic value, double defaultValue) {
    if (value == null) return defaultValue;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? defaultValue;
    return defaultValue;
  }

  Map<String, dynamic> toJson() {
    return {
      'id_booking': idBooking,
      'id_user': idUser,
      'id_petugas': idPetugas,
      'id_room': idRoom,
      'tipe_booking': tipeBooking,
      'harga': harga,
      'durasi': durasi,
      'tanggal_mulai': tanggalMulai,
      'tanggal_selesai': tanggalSelesai,
      'status': status,
      'keterangan': keterangan,
      'created_at': createdAt,
      'updated_at': updatedAt,
      'room': room?.toJson(),
    };
  }
}
