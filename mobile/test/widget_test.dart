import 'package:flutter_test/flutter_test.dart';
import 'package:ukk_room_booking/main.dart';

void main() {
  testWidgets('App should start and render login screen', (WidgetTester tester) async {
    await tester.pumpWidget(const MyApp());
    
    // Verify app initializes correctly
    expect(find.text('Login'), findsOneWidget);
  });
}
