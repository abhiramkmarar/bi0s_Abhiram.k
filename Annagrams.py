word_1=input('Enter first word:')
word_2=input('Enter second wword:')
letters_1=[]
letters_2=[]
for i in word_1.lower():
    letters_1.append(i)
for i in word_2.lower():
    letters_2.append(i)
letters_1.sort()
letters_2.sort()
if letters_1 == letters_2:
    return True
else:
    return False
