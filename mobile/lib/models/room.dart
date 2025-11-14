class Room {
  final int idRoom;
  final String namaRoom;
  final String lokasi;
  final String deskripsi;
  final int kapasitas;
  final double hargaPagi;
  final double hargaSiang;
  final double hargaMalam;
  final String? createdAt;
  final String? updatedAt;

  Room({
    required this.idRoom,
    required this.namaRoom,
    required this.lokasi,
    required this.deskripsi,
    required this.kapasitas,
    this.hargaPagi = 60000,
    this.hargaSiang = 80000,
    this.hargaMalam = 100000,
    this.createdAt,
    this.updatedAt,
  });

  factory Room.fromJson(Map<String, dynamic> json) {
    return Room(
      idRoom: json['id_room'] ?? 0,
      namaRoom: json['nama_room'] ?? '',
      lokasi: json['lokasi'] ?? '',
      deskripsi: json['deskripsi'] ?? '',
      kapasitas: json['kapasitas'] ?? 0,
      hargaPagi: _parseDouble(json['harga_pagi'], 60000),
      hargaSiang: _parseDouble(json['harga_siang'], 80000),
      hargaMalam: _parseDouble(json['harga_malam'], 100000),
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
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
      'id_room': idRoom,
      'nama_room': namaRoom,
      'lokasi': lokasi,
      'deskripsi': deskripsi,
      'kapasitas': kapasitas,
      'harga_pagi': hargaPagi,
      'harga_siang': hargaSiang,
      'harga_malam': hargaMalam,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}