# Meetings Load More Functionality

## Overview
This implementation adds a "Load More" button to the meetings list view, allowing users to load additional meetings without refreshing the page. The functionality follows the same pattern used in other parts of the application like TaskBoard and LeadBoard.

## Features

### Backend Changes
1. **MeetingController.php**:
   - Added pagination support to `index()` method with configurable limit (10 meetings per page)
   - Created new `loadMore()` method to handle additional meeting requests
   - Added pagination data to response (hasMoreMeetings, totalMeetings, currentSkip)

### Frontend Changes
1. **meetings-list.blade.php**:
   - Added load more button with loading states
   - Implemented JavaScript to handle load more requests
   - Added CSS styling for the load more button

2. **meetings-load-more.blade.php**:
   - New view file for rendering additional meetings
   - Reuses the same meeting card structure as the main list

3. **list-view.blade.php**:
   - Updated loadData function to handle load more responses
   - Maintains button state across filter changes

### Routes
- Added `GET /meetings/load-more` route in `Modules/Performance/Routes/web.php`

## How It Works

1. **Initial Load**: The meetings list loads with the first 10 meetings
2. **Load More Button**: Appears if there are more meetings available
3. **User Interaction**: Clicking the button loads the next 10 meetings
4. **Append Content**: New meetings are appended to the existing list
5. **Button State**: Button is hidden when all meetings are loaded

## Configuration

### Pagination Limit
The number of meetings loaded per request can be configured in `MeetingController.php`:
```php
$this->meetingsPerPage = 10; // Change this value as needed
```

### Filters Support
The load more functionality maintains all existing filters:
- Status (upcoming, pending, recurring, past, cancelled)
- Employee filter
- Month/Year filter
- Search text

## Benefits

1. **Performance**: Faster initial page load with fewer database queries
2. **User Experience**: Smooth loading without page refreshes
3. **Scalability**: Handles large datasets efficiently
4. **Consistency**: Follows established application patterns

## Testing

To test the functionality:
1. Create more than 10 meetings
2. Navigate to the meetings list
3. Verify the "Load More" button appears
4. Click the button and verify additional meetings load
5. Test with various filters to ensure they work correctly

## Browser Compatibility
- Works with all modern browsers
- Graceful fallback for JavaScript-disabled environments
- Responsive design for mobile devices 