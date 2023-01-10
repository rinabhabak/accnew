Alpine_OrdersExport module provides export reports from native admin sales/order grid in CSV text format.

Added options under export dropdown from the right:

1.Export report #1

2.Export report #2

Format of profile #1:

Slide Series,Account Number,Cost Center,Company Number,Subaccount,Project Number,Revenue
3600,30300,075,116,000000,0000,$12,050.00

Format of profile #2 - almost the same as profile #1 with addition of BBU filed before Revenue:

Slide Series,Account Number,Cost Center,Company Number,Subaccount,Project Number,BBU,Revenue
3600,30300,075,116,000000,0000, 1400,$12,050.00

3.Filters above the grid are respected: date range, order status, etc.

4.Both profiles group order items by slide series product attribute purchased during specific date range.

5.BBU column is a summation of all BBU rate of each product * qty purchased. 

This means that, IF a group has a quantity of 3 units sold for one product with a BBU of 4, THEN the total BBU for that group is 12.
