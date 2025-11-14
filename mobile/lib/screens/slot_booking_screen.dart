import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../models/room.dart';
import '../utils/constants.dart';
import '../utils/auth_storage.dart';
import 'package:intl/intl.dart';

class SlotBookingScreen extends StatefulWidget {
  const SlotBookingScreen({Key? key}) : super(key: key);

  @override
  _SlotBookingScreenState createState() => _SlotBookingScreenState();
}

class _SlotBookingScreenState extends State<SlotBookingScreen> {
  List<Room> _rooms = [];
  List<Room> _filteredRooms = [];
  bool _loading = true;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadRooms();
  }

  void _filterRooms() {
    setState(() {
      _filteredRooms = _rooms.where((room) {
        return room.namaRoom.toLowerCase().contains(_searchQuery.toLowerCase()) ||
            room.deskripsi.toLowerCase().contains(_searchQuery.toLowerCase());
      }).toList();
    });
  }

  Future<void> _loadRooms() async {
    try {
      final token = await AuthStorage.getToken();
      final response = await http.get(
        Uri.parse('${AppConstants.apiUrl}/slot-rooms'),
        headers: {
          if (token != null) 'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (mounted) {
          setState(() {
            _rooms = (data['data'] as List).map((r) => Room.fromJson(r)).toList();
            _filteredRooms = _rooms;
            _loading = false;
          });
        }
      } else {
        if (mounted) {
          setState(() => _loading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Gagal memuat data'), backgroundColor: Colors.red),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}'), backgroundColor: Colors.red),
        );
      }
    }
  }

  void _openBookingForm(Room room) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => BookingFormScreen(room: room)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final availableRooms = _filteredRooms.length;
    final totalRooms = _rooms.length;
    
    return Scaffold(
      backgroundColor: Color(0xFFFAF3E0),
      body: CustomScrollView(
        slivers: [
          // Custom App Bar dengan gradient
          SliverAppBar(
            expandedHeight: 180,
            floating: false,
            pinned: true,
            backgroundColor: Color(0xFFFAF3E0),
            elevation: 0,
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                children: [
                  // Gradient header
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFFFF9800), Color(0xFFFF6F00)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(30),
                        bottomRight: Radius.circular(30),
                      ),
                    ),
                  ),
                  // Wave pattern
                  Positioned(
                    bottom: -10,
                    left: 0,
                    right: 0,
                    child: CustomPaint(
                      size: Size(MediaQuery.of(context).size.width, 40),
                      painter: WavePainter(),
                    ),
                  ),
                  // Content
                  SafeArea(
                    child: Padding(
                      padding: EdgeInsets.fromLTRB(20, 20, 20, 0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(Icons.meeting_room_rounded, color: Colors.white, size: 28),
                              SizedBox(width: 12),
                              Text(
                                'Pilih Ruangan',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 24,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                          SizedBox(height: 8),
                          Text(
                            'Temukan ruangan terbaik untuk kebutuhan Anda',
                            style: TextStyle(color: Colors.white.withOpacity(0.9), fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          
          // Stats Cards
          SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.fromLTRB(16, 20, 16, 0),
              child: Row(
                children: [
                  Expanded(
                    child: _buildStatCard(
                      'Total Ruangan',
                      totalRooms.toString(),
                      Icons.meeting_room,
                      Color(0xFF2196F3),
                    ),
                  ),
                  SizedBox(width: 12),
                  Expanded(
                    child: _buildStatCard(
                      'Tersedia',
                      availableRooms.toString(),
                      Icons.check_circle,
                      Color(0xFF4CAF50),
                    ),
                  ),
                ],
              ),
            ),
          ),
          
          // Search Bar
          SliverToBoxAdapter(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(15),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.08),
                      blurRadius: 10,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
                child: TextField(
                  onChanged: (value) {
                    setState(() {
                      _searchQuery = value;
                      _filterRooms();
                    });
                  },
                  decoration: InputDecoration(
                    hintText: 'Cari ruangan...',
                    prefixIcon: Icon(Icons.search, color: Color(0xFFFF8C00)),
                    suffixIcon: _searchQuery.isNotEmpty
                        ? IconButton(
                            icon: Icon(Icons.clear, color: Colors.grey),
                            onPressed: () {
                              setState(() {
                                _searchQuery = '';
                                _filterRooms();
                              });
                            },
                          )
                        : null,
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  ),
                ),
              ),
            ),
          ),
          
          // Room Grid
          _loading
              ? SliverFillRemaining(
                  child: Center(
                    child: SizedBox(
                      width: 80,
                      height: 80,
                      child: CircularProgressIndicator(
                        color: Color(0xFFFF8C00),
                        strokeWidth: 4,
                      ),
                    ),
                  ),
                )
              : _filteredRooms.isEmpty
                  ? SliverFillRemaining(
                      child: Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.search_off, size: 64, color: Colors.grey),
                            SizedBox(height: 16),
                            Text(
                              'Tidak ada ruangan ditemukan',
                              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
                            ),
                          ],
                        ),
                      ),
                    )
                  : SliverPadding(
                      padding: EdgeInsets.all(16),
                      sliver: SliverGrid(
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: MediaQuery.of(context).size.width > 800 ? 4 : 2,
                          childAspectRatio: 0.75,
                          crossAxisSpacing: 14,
                          mainAxisSpacing: 14,
                        ),
                        delegate: SliverChildBuilderDelegate(
                          (context, index) => _buildRoomCard(_filteredRooms[index]),
                          childCount: _filteredRooms.length,
                        ),
                      ),
                    ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(15),
        boxShadow: [
          BoxShadow(
            color: color.withOpacity(0.15),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF6D4C41),
                  ),
                ),
                Text(
                  label,
                  style: TextStyle(fontSize: 11, color: Colors.grey[600]),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRoomCard(Room room) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: LinearGradient(
          colors: [Colors.white, Color(0xFFFFFBF5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.brown.withOpacity(0.1),
            blurRadius: 12,
            offset: Offset(0, 4),
            spreadRadius: 2,
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _openBookingForm(room),
          borderRadius: BorderRadius.circular(20),
          splashColor: Color(0xFFFF9800).withOpacity(0.1),
          highlightColor: Color(0xFFFF9800).withOpacity(0.05),
          child: Padding(
            padding: EdgeInsets.all(14),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Icon Header dengan badge
                Stack(
                  alignment: Alignment.center,
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: LinearGradient(
                          colors: [Color(0xFFFF9800), Color(0xFFFF6F00)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Color(0xFFFF9800).withOpacity(0.4),
                            blurRadius: 10,
                            offset: Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Icon(Icons.meeting_room_rounded, color: Colors.white, size: 32),
                    ),
                    Positioned(
                      right: 0,
                      top: 0,
                      child: Container(
                        padding: EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: Color(0xFF4CAF50),
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 2),
                        ),
                        child: Icon(Icons.check, color: Colors.white, size: 12),
                      ),
                    ),
                  ],
                ),
                SizedBox(height: 12),
                
                // Room Name
                Text(
                  room.namaRoom,
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF6D4C41),
                    letterSpacing: 0.3,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                SizedBox(height: 4),
                
                // Description dengan icon
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.location_on, size: 11, color: Colors.grey[500]),
                    SizedBox(width: 3),
                    Expanded(
                      child: Text(
                        room.deskripsi,
                        style: TextStyle(fontSize: 10, color: Colors.grey[600]),
                        textAlign: TextAlign.center,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                SizedBox(height: 10),
                
                // Divider
                Container(
                  height: 1,
                  margin: EdgeInsets.symmetric(horizontal: 8),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Colors.transparent,
                        Colors.grey.withOpacity(0.3),
                        Colors.transparent,
                      ],
                    ),
                  ),
                ),
                SizedBox(height: 10),
                
                // Price Container
                Container(
                  padding: EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Color(0xFFFFF8E1), Color(0xFFFFECB3)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: Color(0xFFFFE082), width: 1),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.access_time, size: 11, color: Color(0xFFFF8F00)),
                          SizedBox(width: 4),
                          Text(
                            'Harga per Jam',
                            style: TextStyle(
                              fontSize: 9.5,
                              color: Color(0xFFE65100),
                              fontWeight: FontWeight.w700,
                              letterSpacing: 0.3,
                            ),
                          ),
                        ],
                      ),
                      SizedBox(height: 6),
                      _buildPriceRow('🌅 Pagi', room.hargaPagi, Color(0xFF4CAF50)),
                      SizedBox(height: 3),
                      _buildPriceRow('☀️ Siang', room.hargaSiang, Color(0xFFFF9800)),
                      SizedBox(height: 3),
                      _buildPriceRow('🌙 Malam', room.hargaMalam, Color(0xFFE53935)),
                    ],
                  ),
                ),
                SizedBox(height: 8),
                
                // Book button
                Container(
                  height: 32,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Color(0xFFFF9800), Color(0xFFFF6F00)],
                    ),
                    borderRadius: BorderRadius.circular(8),
                    boxShadow: [
                      BoxShadow(
                        color: Color(0xFFFF9800).withOpacity(0.3),
                        blurRadius: 6,
                        offset: Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Material(
                    color: Colors.transparent,
                    child: InkWell(
                      onTap: () => _openBookingForm(room),
                      borderRadius: BorderRadius.circular(8),
                      child: Center(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.calendar_today, color: Colors.white, size: 14),
                            SizedBox(width: 6),
                            Text(
                              'Booking Sekarang',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
  
  Widget _buildPriceRow(String label, double price, Color color) {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 10,
              color: color,
              fontWeight: FontWeight.w700,
            ),
          ),
          Text(
            'Rp ${NumberFormat('#,###', 'id').format(price)}',
            style: TextStyle(
              fontSize: 10,
              color: color,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }
}

// Custom Wave Painter untuk header
class WavePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Color(0xFFFAF3E0)
      ..style = PaintingStyle.fill;

    final path = Path()
      ..moveTo(0, size.height * 0.5)
      ..quadraticBezierTo(
        size.width * 0.25,
        size.height * 0.2,
        size.width * 0.5,
        size.height * 0.5,
      )
      ..quadraticBezierTo(
        size.width * 0.75,
        size.height * 0.8,
        size.width,
        size.height * 0.5,
      )
      ..lineTo(size.width, size.height)
      ..lineTo(0, size.height)
      ..close();

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

// Form Booking Screen
class BookingFormScreen extends StatefulWidget {
  final Room room;
  const BookingFormScreen({Key? key, required this.room}) : super(key: key);

  @override
  _BookingFormScreenState createState() => _BookingFormScreenState();
}

class _BookingFormScreenState extends State<BookingFormScreen> {
  DateTime _selectedDate = DateTime.now();
  String? _jamMulai;
  String? _jamSelesai;
  final _descController = TextEditingController();
  double _totalHarga = 0;
  int _durasi = 0;
  bool _isSubmitting = false;

  final List<String> _jamList = List.generate(19, (index) => '${(index + 6).toString().padLeft(2, '0')}:00');

  void _calculatePrice() {
    if (_jamMulai == null || _jamSelesai == null) return;

    int start = int.parse(_jamMulai!.split(':')[0]);
    int end = int.parse(_jamSelesai!.split(':')[0]);

    if (end <= start) {
      setState(() {
        _totalHarga = 0;
        _durasi = 0;
      });
      return;
    }

    double total = 0;
    for (int hour = start; hour < end; hour++) {
      if (hour >= 6 && hour < 12) {
        total += widget.room.hargaPagi;
      } else if (hour >= 12 && hour < 18) {
        total += widget.room.hargaSiang;
      } else {
        total += widget.room.hargaMalam;
      }
    }

    setState(() {
      _totalHarga = total;
      _durasi = end - start;
    });
  }

  Future<void> _bookSlot() async {
    print('🔥 BOOKING DIMULAI');
    
    if (_isSubmitting) {
      print('⚠️ Masih submitting, dibatalkan');
      return;
    }
    
    print('✅ Jam mulai: $_jamMulai');
    print('✅ Jam selesai: $_jamSelesai');
    print('✅ Keterangan: ${_descController.text}');
    
    if (_jamMulai == null || _jamSelesai == null) {
      print('❌ Jam belum diisi');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Lengkapi semua data!'), backgroundColor: Colors.red),
      );
      return;
    }

    if (_descController.text.trim().isEmpty) {
      print('❌ Keterangan kosong');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Catatan harus diisi!'), backgroundColor: Colors.red),
      );
      return;
    }

    int start = int.parse(_jamMulai!.split(':')[0]);
    int end = int.parse(_jamSelesai!.split(':')[0]);

    if (end <= start) {
      print('❌ Jam selesai <= jam mulai');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Jam selesai harus lebih dari jam mulai!'), backgroundColor: Colors.red),
      );
      return;
    }

    print('🚀 Mulai kirim request...');
    setState(() => _isSubmitting = true);

    try {
      final token = await AuthStorage.getToken();
      if (token == null || token.isEmpty) {
        print('🔑 Token tidak ditemukan!');
        if (mounted) {
          setState(() => _isSubmitting = false);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Sesi berakhir. Silakan login ulang.'), backgroundColor: Colors.red),
          );
        }
        return;
      }
      print('🔑 Token: ${token.substring(0, 20)}...');
      
      final requestBody = {
        'id_room': widget.room.idRoom,
        'tanggal': DateFormat('yyyy-MM-dd').format(_selectedDate),
        'jam_mulai': _jamMulai,
        'jam_selesai': _jamSelesai,
        'keterangan': _descController.text.trim(),
      };
      
      print('📦 Request body: $requestBody');
      
      final response = await http.post(
        Uri.parse('${AppConstants.apiUrl}/slot-bookings'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode(requestBody),
      );

      print('📥 Response status: ${response.statusCode}');
      print('📥 Response headers: ${response.headers}');
      print('📥 Response body (raw): ${response.body.substring(0, response.body.length > 500 ? 500 : response.body.length)}');
      
      Map<String, dynamic> data = {};
      try {
        data = json.decode(response.body) as Map<String, dynamic>;
      } catch (_) {
        // Jika bukan JSON, pakai pesan default
        data = {
          'success': false,
          'message': 'Server mengembalikan format tidak valid. Coba lagi atau hubungi admin.'
        };
      }
      
      if (mounted) {
        setState(() => _isSubmitting = false);
        
        if (response.statusCode == 201) {
          print('✅ BOOKING BERHASIL!');
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('✅ ${data['message']}'), backgroundColor: Colors.green),
          );
          // Tutup form saja untuk menghindari white screen akibat pop stack berlebih di Web
          if (Navigator.of(context).canPop()) {
            Navigator.of(context).pop();
          } else {
            // Jika tidak bisa pop (jarang terjadi), arahkan ke beranda agar aman
            Navigator.of(context).pushReplacementNamed('/home');
          }
        } else {
          print('❌ BOOKING GAGAL (${response.statusCode}): ${data['message']}');
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('❌ (${response.statusCode}) ${data['message'] ?? 'Gagal booking'}'), backgroundColor: Colors.red),
          );
        }
      }
    } catch (e) {
      print('💥 ERROR EXCEPTION: $e');
      if (mounted) {
        setState(() => _isSubmitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('❌ Error: ${e.toString()}'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Color(0xFFFAF3E0),
      appBar: AppBar(
        title: Text('Form Booking'),
        backgroundColor: Color(0xFFFAF3E0),
        elevation: 0,
        foregroundColor: Colors.brown[800],
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Column(
                children: [
                  Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: LinearGradient(
                        colors: [Color(0xFFFF9800), Color(0xFFFF6F00)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.orange.withOpacity(0.4),
                          blurRadius: 12,
                          offset: Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Icon(Icons.edit_calendar, color: Colors.white, size: 40),
                  ),
                  SizedBox(height: 16),
                  Text('Form Booking', style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Color(0xFF8B4513))),
                  SizedBox(height: 8),
                  Text('Isi detail booking Anda untuk menyelesaikan reservasi', style: TextStyle(color: Colors.grey[600]), textAlign: TextAlign.center),
                ],
              ),
            ),
            SizedBox(height: 32),

            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.info_outline, color: Color(0xFF8B4513), size: 24),
                        SizedBox(width: 8),
                        Text('Ringkasan Booking', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF8B4513))),
                      ],
                    ),
                    SizedBox(height: 16),
                    _buildInfoRow('Ruangan:', widget.room.namaRoom, isHighlight: true),
                    SizedBox(height: 8),
                    _buildInfoRow('Lokasi:', widget.room.lokasi),
                    SizedBox(height: 8),
                    _buildInfoRow('Tanggal:', DateFormat('dd MMM yyyy', 'id_ID').format(_selectedDate)),
                    if (_jamMulai != null && _jamSelesai != null) ...[
                      SizedBox(height: 8),
                      _buildInfoRow('Waktu:', '$_jamMulai - $_jamSelesai', color: Color(0xFFE53935)),
                    ],
                    if (_durasi > 0) ...[
                      SizedBox(height: 8),
                      _buildInfoRow('Durasi:', '$_durasi jam', isHighlight: true),
                      SizedBox(height: 16),
                      Container(
                        padding: EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Color(0xFFFFF8E1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('Total Harga:', style: TextStyle(fontWeight: FontWeight.bold)),
                            Text('Rp ${NumberFormat('#,###', 'id').format(_totalHarga)}', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFFFF8C00))),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
            SizedBox(height: 24),

            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.calendar_today, color: Color(0xFF8B4513)),
                        SizedBox(width: 8),
                        Text('Pilih Tanggal', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF8B4513))),
                      ],
                    ),
                    SizedBox(height: 12),
                    InkWell(
                      onTap: () async {
                        final date = await showDatePicker(
                          context: context,
                          initialDate: _selectedDate,
                          firstDate: DateTime.now(),
                          lastDate: DateTime.now().add(Duration(days: 90)),
                        );
                        if (date != null) setState(() => _selectedDate = date);
                      },
                      child: Container(
                        padding: EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Color(0xFFFFF8E1),
                          border: Border.all(color: Color(0xFFFF9800)),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.event, color: Color(0xFFFF8C00)),
                            SizedBox(width: 12),
                            Text(DateFormat('dd MMMM yyyy', 'id_ID').format(_selectedDate), style: TextStyle(fontSize: 16, fontWeight: FontWeight.w500)),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            SizedBox(height: 16),

            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.access_time, color: Color(0xFF8B4513)),
                        SizedBox(width: 8),
                        Text('Pilih Waktu', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF8B4513))),
                      ],
                    ),
                    SizedBox(height: 12),
                    Text('Durasi Booking', style: TextStyle(fontSize: 14, color: Colors.grey[700])),
                    SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            value: _jamMulai,
                            decoration: InputDecoration(
                              labelText: 'Jam Mulai',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                              contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            ),
                            items: _jamList.map((jam) => DropdownMenuItem(value: jam, child: Text(jam))).toList(),
                            onChanged: (val) {
                              setState(() => _jamMulai = val);
                              _calculatePrice();
                            },
                          ),
                        ),
                        SizedBox(width: 12),
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            value: _jamSelesai,
                            decoration: InputDecoration(
                              labelText: 'Jam Selesai',
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                              contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            ),
                            items: _jamList.skip(1).toList().map((jam) => DropdownMenuItem(value: jam, child: Text(jam))).toList()
                              ..add(DropdownMenuItem(value: '24:00', child: Text('24:00'))),
                            onChanged: (val) {
                              setState(() => _jamSelesai = val);
                              _calculatePrice();
                            },
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            SizedBox(height: 24),

            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              child: Padding(
                padding: EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.people, color: Color(0xFF8B4513)),
                        SizedBox(width: 8),
                        Text('Detail Penyewa', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF8B4513))),
                      ],
                    ),
                    SizedBox(height: 12),
                    Text('Catatan Tambahan', style: TextStyle(fontSize: 14, color: Colors.grey[700])),
                    SizedBox(height: 8),
                    TextField(
                      controller: _descController,
                      maxLines: 4,
                      decoration: InputDecoration(
                        hintText: 'Catatan khusus, permintaan, atau informasi tambahan...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        filled: true,
                        fillColor: Color(0xFFFFF8E1),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            SizedBox(height: 32),

            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Color(0xFF6B7280),
                      side: BorderSide(color: Color(0xFF6B7280)),
                      padding: EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.arrow_back, size: 18),
                        SizedBox(width: 8),
                        Text('Kembali', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      ],
                    ),
                  ),
                ),
                SizedBox(width: 16),
                Expanded(
                  flex: 2,
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _bookSlot,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _isSubmitting ? Colors.grey : Color(0xFFFF8C00),
                      foregroundColor: Colors.white,
                      padding: EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      elevation: 4,
                    ),
                    child: _isSubmitting
                        ? Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                  strokeWidth: 2,
                                ),
                              ),
                              SizedBox(width: 12),
                              Text('Memproses...', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                            ],
                          )
                        : Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.check_circle, size: 18),
                              SizedBox(width: 8),
                              Text('Booking Sekarang', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                            ],
                          ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, {bool isHighlight = false, Color? color}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(color: Colors.grey[700])),
        Text(value, style: TextStyle(fontWeight: isHighlight ? FontWeight.bold : FontWeight.w500, color: color ?? (isHighlight ? Color(0xFF8B4513) : Colors.black87))),
      ],
    );
  }
}
