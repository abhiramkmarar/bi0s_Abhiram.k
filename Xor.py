integers = []
while True:
    user_int = int(input('ENTER THE INTEGER:'))
    integers.append(user_int)
    e = input('ADD MORE?(Y/N):')
    if e.lower() == 'n':
        if len(integers) < 2:
            print('Please enter at least 2 integers:')
        else:
            break

target = int(input('ENTER THE TARGET:'))
len_integers = len(integers)
xor_pair = []

for i in range(len_integers):
    for j in range(i + 1, len_integers):
        xor_val = integers[i] ^ integers[j]
        if xor_val == target:
            t1 = (integers[i], integers[j])
            xor_pair.append(t1)

if len(xor_pair) == 0:
    print('No pairs exist')
else:
    print('THE PAIR OF INTEGERS WHOSE XOR IS EQUAL TO THE TARGET ARE:', xor_pair)
