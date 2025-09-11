str_user=input('ENTER THE STRING:')
    alpha=[]
    for i in str_user:
        if i.isalpha():
            alpha.append(i)
    alpha.reverse()
    alpha_index=0
    reversed_str=''

    for i in range(len(str_user)):
        if str_user[i].isalpha():
            reversed_str=reversed_str+str(alpha[alpha_index])
            alpha_index=alpha_index+1
        else:
            reversed_str=reversed_str+str_user[i]
    print('ALPHABETICALLY REVERSED STRING:',reversed_str)
